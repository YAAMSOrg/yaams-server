@extends('layouts.loginlayout')
@section('title', 'Register as pilot')
@section('content')

    <div class="text-center">

    <form action="{{ route('register') }}" class="form-signin" method="post">
        @csrf
      <i class="fas fa-plane-departure fa-5x"></i>
      <h1 class="h3 mb-3 font-weight-normal">Register as a new pilot</h1>
        @if(session('status'))
          <div class="alert alert-danger" role="alert">
            {{ session('status') }}
          </div>
        @endif

      <label for="email" class="sr-only">Email address</label>
          @error('email')
          <div class="alert alert-danger" role="alert" style="margin-top: 5px; margin-bottom: 5px;">
              {{ $message }}
          </div>
          @enderror
      <input type="email" id="email" class="form-control" name="email" placeholder="Email address" required autofocus value="{{ old('email') }}">

      <label for="name" class="sr-only">Full name</label>
          @error('email')
          <div class="alert alert-danger" role="alert" style="margin-top: 5px; margin-bottom: 5px;">
              {{ $message }}
          </div>
          @enderror
      <input type="text" id="name" class="form-control" name="name" placeholder="John Doe" required value="{{ old('name') }}">

     
      <label for="homebase" class="sr-only">Home base (home airport)</label>
          @error('homebase')
          <div class="alert alert-danger" role="alert" style="margin-top: 5px; margin-bottom: 5px;">
              {{ $message }}
          </div>
          @enderror
      <input type="text" id="homebase" class="form-control" name="homebase" placeholder="EDDK" required value="{{ old('homebase') }}">
 
      <label for="password" class="sr-only">Password</label>
      @error('password')
      <div class="alert alert-danger" role="alert" style="margin-top: 5px; margin-bottom: 5px;">
          {{ $message }}
      </div>
      @enderror
      <input type="password" name="password" id="password" placeholder="Your password" class="form-control" value="">

      <label for="password_confirmation" class="sr-only">Confirm your password</label>
      @error('password')
      <div class="alert alert-danger" role="alert" style="margin-top: 5px; margin-bottom: 5px;">
          {{ $message }}
      </div>
      @enderror
      <input type="password" name="password_confirmation" id="password_confirmation" placeholder="Your password" class="form-control form-control-lg" value="">

      <button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>
      <button type="reset" class="btn btn-lg btn-secondary btn-block">Reset</button>
    </form>
            </div>
    </div>

@endsection