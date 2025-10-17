<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResgisterBingoUserRequest;
use App\Http\Requests\UpdateBingoUserRequest;
use App\Models\BingoUser;
use App\Models\BingoUserBoard;
use App\Services\BingoUserBoardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BingoUserController extends Controller
{
    private BingoUser $bingoUser;
    private BingoUserBoardService $bingoUserBoardService;
    private $authBingoUser;

    public function __construct(
        BingoUser $bingoUser,
        BingoUserBoardService $bingoUserBoardService
    )
    {
        $this->bingoUser = $bingoUser;
        $this->bingoUserBoardService = $bingoUserBoardService;
        $this->authBingoUser = Auth('bingo')->user();
    }

    /**
     * View login
     */
    public function index(): View
    {
        return view('bingo-user.resgister');
    }

    /**
     * Resgister bingo user
     */
    public function resgister(ResgisterBingoUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();
            $bingoUser = $this->bingoUser->create($data);

            DB::commit();

            Auth::guard('bingo')->login($bingoUser);

            return $this->success($bingoUser, __('view.notify.bingo_user.resgister_success'));
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), null, __('view.notify.error'));
        }
    }

    /**
     * Check User bingo by infomation
     */
    public function checkUser(Request $request): JsonResponse
    {
        $data = $request->only(['email', 'phone_number']);

        $user = $this->bingoUser
            ->where('email', $data['email'])
            ->where('phone_number', $data['phone_number'])
            ->first();

        if ($user) {
            Auth::guard('bingo')->login($user);

            return $this->success($user, __('view.notify.bingo_user.login_success'));
        }

        return $this->error(null, null, __('view.notify.bingo_user.null_account'));
    }

    public function detail(BingoUser $bingoUser)
    {
        return view('bingo-user.detail', compact('bingoUser'));
    }

    /**
     * Update infomation bingo user
     */
    public function update(UpdateBingoUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $bingoUser = auth('bingo')->user();

            /** @var \App\Models\BingoUser $bingoUser */
            $bingoUser->fill($data);

            if ($bingoUser->isDirty()) {
                $bingoUser->update($data);
            }

            DB::commit();

            return $this->success($bingoUser, __('view.notify.success'));
        } catch (Exception $e) {
            DB::rollBack();

            return $this->error($e->getMessage(), null, __('view.notify.error'));
        }
    }

    /**
     * Save Board game
     *
     * @return void
     */
    public function saveBoardGame(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try{
            $bingoBoard = !empty($request->bingo_board) ? $request->bingo_board : [];
            $markedCells = !empty($request->marked_cells) ? $request->marked_cells : [];
            $bingoUser = Auth('bingo')->user();
            $result = $this->bingoUserBoardService->create($bingoBoard, $markedCells, $bingoUser);
            if ($result){
                DB::commit();
            }
            return $this->success();
        } catch(\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->error(false, "Sever error!!!");
        }
    }

    /**
     * Fetch user bingo game
     * @return JsonResponse
     */
    public function fetchBingoUserBoard(): JsonResponse
    {
        $bingoUser = $this->authBingoUser;
        $bingoUserBoard = $this->bingoUserBoardService->fetchBingoUserBoard($bingoUser);
        dd($bingoUserBoard);
        return $this->success();
    }
}
