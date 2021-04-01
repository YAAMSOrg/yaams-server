@extends('layouts.loginlayout')

@section('content')

    <div class="text-center">



    <form action="{{ route('login') }}" class="form-signin" method="post">
        @csrf
      <i class="fas fa-plane-departure fa-5x"></i>
      <h1 class="h3 mb-3 font-weight-normal">Sign in to Yaams</h1>
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
      <label for="password" class="sr-only">Password</label>
      @error('password')
      <div class="alert alert-danger" role="alert" style="margin-top: 5px; margin-bottom: 5px;">
          {{ $message }}
      </div>
      @enderror
      <input type="password" name="password" id="password" placeholder="Your password" class="form-control form-control-lg" value="">
      <div class="checkbox mb-3">
        <label>
            <input type="checkbox" name="remember" id="remember" class="form-check-input"> Remember me
        </label>
      </div>
      <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
      <button type="submit" class="btn btn-lg btn-secondary btn-block">Forgot password?</button>
      <p class="mt-5 mb-3 text-muted"><a href="{{ route('register') }}">Register here</a></p>
    </form>
            </div>
    </div>


@endsection
