<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterBingoUserRequest;
use App\Http\Requests\UpdateBingoUserRequest;
use App\Models\BingoUser;
use App\Services\BingoUserBoardService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Throwable;

class BingoUserController extends Controller
{
    private BingoUser $bingoUser;
    private BingoUserBoardService $bingoUserBoardService;

    public function __construct(
        BingoUser $bingoUser,
        BingoUserBoardService $bingoUserBoardService
    )
    {
        $this->bingoUser = $bingoUser;
        $this->bingoUserBoardService = $bingoUserBoardService;
    }

    /**
     * View register
     *
     * @return View
     */
    public function index(): View
    {
        return view('bingo-user.register');
    }

    /**
     * Register or login bingo user
     *
     * @param RegisterBingoUserRequest $request
     * @return JsonResponse
     */
    public function registerOrLogin(RegisterBingoUserRequest $request): JsonResponse
    {
        try {
            $data = $request->validated();

            $user = DB::transaction(
                fn() =>
                $this->bingoUser->
                    updateOrCreate(
                        [
                            'email' => $data['email'] ?? null,
                        ],
                        $data
                    )
            );

            Auth::guard('bingo')->login($user);

            return $this->success(
                $user,
                $user->wasRecentlyCreated
                ? __('view.notify.bingo_user.register_success')
                : __('view.notify.bingo_user.login_success')
            );
        } catch (Throwable $e) {
            return $this->error($e->getMessage(), null, __('view.notify.error'));
        }
    }

    /**
     * Check User bingo by information
     *
     * @param Request $request
     * @return JsonResponse
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

    /**
     * Redirect view detail
     *
     * @param BingoUser $bingoUser
     * @return View
     */
    public function detail(BingoUser $bingoUser): View
    {
        return view('bingo-user.detail', compact('bingoUser'));
    }

    /**
     * Update information bingo user
     *
     * @param UpdateBingoUserRequest $request
     * @return JsonResponse
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

            Log::error($e->getMessage());
            return $this->error($e->getMessage(), null, __('view.notify.error'));
        }
    }

    /**
     * Save Board game
     *
     * @param Request $request
     * @return void
     */
    public function saveBoardGame(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $bingoBoard = !empty($request->bingo_board) ? $request->bingo_board : [];
            $markedCells = !empty($request->marked_cells) ? $request->marked_cells : [];
            $bingoUser = Auth('bingo')->user();
            $checkBoardGameNotEnd = $this->bingoUserBoardService->fetchBingoUserBoard($bingoUser);
            $result = false;
            if(!$checkBoardGameNotEnd) {
                $result = $this->bingoUserBoardService->create($bingoBoard, $markedCells, $bingoUser);
                if ($result['status']) {
                    DB::commit();
                    return $this->success([
                        'status' => $result, 
                        'bingo_user_board_id' => $result['id']], 
                        __('view.message.successfully_saved'));
                }
            }
            return $this->success(['status' => $result]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->error(null, __('view.message.server_error'));
        }
    }

    /**
     * Fetch user bingo game
     * @return JsonResponse
     */
    public function fetchBingoUserBoard(): JsonResponse
    {
        $bingoUser = Auth('bingo')->user();
        $bingoUserBoard = $this->bingoUserBoardService->fetchBingoUserBoard($bingoUser);
        return $this->success($bingoUserBoard, __('view.message.successfully_found'));
    }

    /**
     * Reset board game
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function resetBoardGame(Request $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $bingoUser = Auth('bingo')->user();
            $checkGameAllowedReset = $this->bingoUserBoardService->checkGameAllowedReset($bingoUser);
            if(!$checkGameAllowedReset) {
                return $this->error(null, null, __('view.message.now_allow_reset'), 400);
            }
            $this->bingoUserBoardService->resetBoardGame($bingoUser, $request->all());
            DB::commit();
            return $this->success(['bingo_user' => $bingoUser], __('view.message.successfully_reset'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e);
            return $this->error(null, __('view.message.server_error'));
        }
    }
}
