<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-2xl px-4 sm:px-6 lg:px-8">
            
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Add New User</h1>
                    <p class="mt-1 text-sm text-gray-600">Create a new employee account and assign their position.</p>
                </div>
                <a href="{{ route('users.index') }}" class="text-sm font-medium text-amber-800 hover:text-amber-900">
                    &larr; Back to Users
                </a>
            </div>

            {{-- Validation errors --}}
            @if ($errors->any())
            <div class="mb-4 rounded-lg bg-red-100 p-3 text-red-800">
                <p class="font-semibold">Please check the form:</p>
                <ul class="mt-2 list-inside list-disc text-sm">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <div class="rounded-xl border border-gray-200 bg-white shadow-sm overflow-hidden">
                <div class="p-6">
                    <form action="{{ route('users.store') }}" method="POST">
                        @csrf
                        
                        <div class="space-y-5">
                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Full Name</label>
                                <input type="text" name="name" value="{{ old('name') }}" required class="w-full rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Email Address</label>
                                <input type="email" name="email" value="{{ old('email') }}" required class="w-full rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Initial Password</label>
                                <input type="password" name="password" required minlength="8" class="w-full rounded-lg border-gray-300">
                            </div>

                            <div>
                                <label class="mb-1 block text-sm font-medium text-gray-700">Assigned Position</label>
                                <select name="role_id" required class="w-full rounded-lg border-gray-300">
                                    <option value="" disabled selected>Select a position</option>
                                    @foreach($roles as $role)
                                        <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mt-8 border-t border-gray-100 pt-5">
                            <button type="submit" class="w-full rounded-lg bg-amber-800 px-4 py-2.5 text-sm font-semibold text-white hover:bg-amber-900 shadow-sm">
                                Create User Account
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>