<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterBingoUserRequest;
use App\Http\Requests\UpdateBingoUserRequest;
use App\Models\BingoUser;
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

    public function __construct(BingoUser $bingoUser)
    {
        $this->bingoUser = $bingoUser;
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
     * Register bingo user
     *
     * @param RegisterBingoUserRequest $request
     * @return JsonResponse
     */
    public function registerOrLogin(RegisterBingoUserRequest $request): JsonResponse
    {
        DB::beginTransaction();
        try {
            $data = $request->validated();

            $bingoUser = $this->bingoUser
                ->where('email', $data['email'])
                ->orWhere('phone_number', $data['phone_number'])
                ->first();

            if($bingoUser) {
                Auth::guard('bingo')->login($bingoUser);
                return $this->success($bingoUser, __('view.notify.bingo_user.login_success'));
            }

            $bingoUserRegister = $this->bingoUser->create($data);

            DB::commit();

            Auth::guard('bingo')->login($bingoUserRegister);

            return $this->success($bingoUserRegister, __('view.notify.bingo_user.register_success'));
        } catch (Exception $e) {
            DB::rollBack();

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
}
