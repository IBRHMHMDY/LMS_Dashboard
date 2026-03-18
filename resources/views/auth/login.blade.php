<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LMS Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center h-screen">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">
        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Welcome Back</h1>
            <p class="text-gray-500 mt-2">Sign in to your dashboard</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-50 text-red-600 rounded-lg text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf
            
            <div class="mb-5">
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
            </div>

            <div class="mb-5">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition">
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-amber-600 border-gray-300 rounded focus:ring-amber-500">
                    <span class="ml-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div>

            <button type="submit" 
                class="w-full bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-amber-700 focus:ring-4 focus:ring-amber-200 transition duration-200">
                Sign In
            </button>
        </form>
    </div>

</body>
</html>