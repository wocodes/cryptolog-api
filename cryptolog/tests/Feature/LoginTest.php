<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LoginTest extends TestCase
{

    use RefreshDatabase;

    public function testUserCanLoginSuccessfully()
    {
        $user = factory(User::class)->create();
        $data = ["username" => $user->email, "password" => "password"];

        $response = $this->postJson("api/user/login", $data);

        dd($response->json());
    }


}
