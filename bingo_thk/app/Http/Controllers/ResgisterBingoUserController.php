<?php

namespace App\Http\Controllers;

use App\Http\Requests\ResgisterBingoUserRequest;
use App\Models\BingoUser;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResgisterBingoUserController extends Controller
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
        return view('resgister-bingo');
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

            return $this->error(null, null, __('view.notify.error'));
        }
    }

    /**
     * Check User bingo by infomation
     */
    public function checkUser(Request $request): JsonResponse
    {
        $data = $request->only(['name', 'phone_number', 'session_token']);

        $user = $this->bingoUser
            ->where('name', $data['name'])
            ->where('phone_number', $data['phone_number'])
            ->where('session_token', $data['session_token'])
            ->first();

        if ($user) {
            Auth::guard('bingo')->login($user);

            return $this->success($user, __('view.notify.bingo_user.login_success'));
        }

        return $this->error(null, null, __('view.notify.bingo_user.null_account'));
    }
}
