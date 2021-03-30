@extends('layouts.app')

@section('content')

    <div class="flex justify-center">
            <div class="w-4/12 bg-white p-6 rounded-lg">
                <h1 class="text-4xl font-normal leading-normal mt-0 mb-2 text-black-800">
                    Register
                </h1>
                <p>Enter your details below to register on Yaams</p>
                <form action="{{ route('register') }}" method="post">
                    @csrf
                    <div class="mb-4">
                        <label for="name" class="sr-only">Name</label>
                        <input type="text" name="name" id="name" placeholder="Your full name" class="bg-gray-100 border-2 w-full p-4 rounded-lg mt-4 @error('name')
                            border-red-500 @enderror" value="{{ old('name') }}">

                        @error('name')
                            <div class="text-red-500 mt-2 text-sm">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>
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
                        <label for="homebase" class="sr-only">Airport Homebase</label>
                        <input type="homebase" name="homebase" id="homebase" placeholder="Your home airport ICAO (e.g. EDDK)" class="bg-gray-100 border-2 w-full p-4 rounded-lg mt-4 @error('homebase')
                        border-red-500 @enderror" value="{{ old('homebase') }}">

                        @error('homebase')
                        <div class="text-red-500 mt-2 text-sm">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password" class="sr-only">Password</label>
                        <input type="password" name="password" id="password" placeholder="Choose a password" class="bg-gray-100 border-2 w-full p-4 rounded-lg mt-4 @error('password')
                        border-red-500 @enderror" value="">

                        @error('password')
                        <div class="text-red-500 mt-2 text-sm">
                            {{ $message }}
                        </div>
                        @enderror
                    </div>
                    <div class="mb-4">
                        <label for="password_confirmation" class="sr-only">Password confirmation</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Repeat your password" class="bg-gray-100 border-2 w-full p-4 rounded-lg mt-4 @error('password')
                        border-red-500 @enderror" value="">
                    </div>
                    <div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-3 rounded font-medium w-full">Register</button>
                    </div>
                </form>
            </div>
    </div>


@endsection
