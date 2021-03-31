@extends('layouts.app')

@section('content')

    <div class="flex justify-center">
            <div class="w-4/12 bg-white p-6 rounded-lg">
                <h1 class="text-4xl font-normal leading-normal mt-0 mb-2 text-black-800">
                    Login
                </h1>

                @if(session('status'))
                    <div class="bg-red-500 p-4 rounded-lg mb-2 text-white text-center">
                        {{ session('status') }}
                    </div>
                @endif

                <form action="{{ route('login') }}" method="post">
                    @csrf
                    <div class="mb-4">
                        <label for="email" class="sr-only">Email</label>
                        <input type="text" name="email" id="email" placeholder="Your email address" class="bg-gray-100 border-2 w-full p-4 rounded-lg mt-4 @error('email')
                        border-red-500 @enderror" value="{{ old('email') }}">

                        @error('email')
                        <div class="text-red-500 mt-2 text-sm">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password" class="sr-only">Password</label>
                        <input type="password" name="password" id="password" placeholder="Your password" class="bg-gray-100 border-2 w-full p-4 rounded-lg mt-4 @error('password')
                        border-red-500 @enderror" value="">

                        @error('password')
                        <div class="text-red-500 mt-2 text-sm">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <div class="flex items-center">
                            <input type="checkbox" name="remember" id="remember" class="mr-2">
                            <label for="remember">Remeber me</label>
                        </div>
                    </div>

                    <div class="flex flex-wrap overflow-hidden -mx-2">
                            <div class="my-2 w-1/2 px-2 overflow-hidden">
                                <button type="submit" class="bg-green-500 text-white px-4 py-3 rounded font-medium w-full">Login</button>
                            </div>
                            <div class="my-2 w-1/2 px-2 overflow-hidden">
                                <button type="submit" class="bg-blue-500 text-white px-4 py-3 rounded font-medium w-full">Forgot password?</button>
                            </div>
                    </div>
                </form>
            </div>
    </div>


@endsection
