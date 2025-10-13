<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResgisterBingoUserRequest;
use App\Http\Requests\UpdateBingoUserRequest;
use App\Models\BingoUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class BingoUserController extends Controller
{
    private BingoUser $bingoUser;

    public function __construct(BingoUser $bingoUser)
    {
        $this->bingoUser = $bingoUser;
    }

    /**
     * View login
     */
    public function index(): View
    {
        $bingoUser = Auth::guard('bingo')->user();

        return view('bingo-user.resgister', compact('bingoUser'));
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

    public function update(UpdateBingoUserRequest $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $bingoUser = Auth::guard('bingo')->user();

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
}
