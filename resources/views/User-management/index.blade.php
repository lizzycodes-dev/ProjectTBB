<x-app-layout>
    <div class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">

            {{-- Page heading & Add Button --}}
            <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">
                        User Management
                    </h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Select a user to view their details, update their position, or archive their account.
                    </p>
                </div>
                <a href="{{ route('users.create') }}" class="rounded-lg bg-amber-800 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-900 shadow-sm">
                    + Add New User
                </a>
            </div>

            {{-- Success message --}}
            @if (session('success'))
            <div class="mb-4 rounded-lg bg-green-100 p-3 text-green-800">
                {{ session('success') }}
            </div>
            @endif

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

            <div class="overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-gray-50 text-xs uppercase text-gray-500">
                            <tr>
                                <th class="px-5 py-3">Name</th>
                                <th class="px-5 py-3">Email</th>
                                <th class="px-5 py-3">Position</th>
                                <th class="px-5 py-3 text-right">Details</th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-gray-100">
                            @forelse ($users as $user)
                            {{-- Clickable user row --}}
                            <tr class="hover:bg-amber-50">
                                <td class="px-5 py-4 font-medium text-gray-800">
                                    {{ $user->name }}
                                </td>
                                <td class="px-5 py-4 text-gray-700">
                                    {{ $user->email }}
                                </td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full bg-green-100 px-3 py-1 text-xs font-medium text-green-800">
                                        {{ $user->role->name ?? 'None' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <button
                                        type="button"
                                        class="toggle-user rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-medium text-gray-700 hover:bg-gray-100"
                                        data-target="user-details-{{ $user->id }}"
                                        aria-expanded="false">
                                        <span class="toggle-label">Edit / Archive</span>
                                        <span class="ml-1 toggle-icon">＋</span>
                                    </button>
                                </td>
                            </tr>

                            {{-- Expandable edit details row --}}
                            <tr id="user-details-{{ $user->id }}" class="hidden bg-gray-50">
                                <td colspan="4" class="px-5 py-5">
                                    <div class="rounded-lg border border-gray-200 bg-white p-4 sm:p-5">

                                        <div class="mb-5">
                                            <h2 class="text-base font-semibold text-gray-800">
                                                {{ $user->name }} — Account Details
                                            </h2>
                                            <p class="mt-1 text-xs text-gray-500">
                                                Update credentials or change their assigned position.
                                            </p>
                                        </div>

                                        <form id="update-form-{{ $user->id }}" action="{{ route('users.update', $user->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            
                                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Full Name</label>
                                                    <input type="text" name="name" value="{{ $user->name }}" required class="w-full rounded-lg border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Email Address</label>
                                                    <input type="email" name="email" value="{{ $user->email }}" required class="w-full rounded-lg border-gray-300 text-sm">
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Position</label>
                                                    <select name="role_id" required class="w-full rounded-lg border-gray-300 text-sm">
                                                        @foreach($roles as $role)
                                                            <option value="{{ $role->id }}" @selected($user->role_id == $role->id)>
                                                                {{ $role->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="mb-1 block text-xs font-medium text-gray-600">Reset Password (Optional)</label>
                                                    <input type="password" name="password" placeholder="Leave blank to keep" minlength="8" class="w-full rounded-lg border-gray-300 text-sm">
                                                </div>
                                            </div>
                                        </form>

                                        <div class="mt-6 flex items-center justify-between border-t border-gray-100 pt-4">
                                            {{-- Archive Button (Separate Form to prevent nesting) --}}
                                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to archive this user?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-700 hover:bg-red-50">
                                                    Archive User
                                                </button>
                                            </form>

                                            {{-- Save Update Button --}}
                                            <button type="submit" form="update-form-{{ $user->id }}" class="rounded-lg bg-amber-800 px-4 py-2 text-sm font-semibold text-white hover:bg-amber-900 shadow-sm">
                                                Save Changes
                                            </button>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-5 py-8 text-center text-gray-500">
                                    No active users found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Keeping your exact same JavaScript logic for the accordion --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.toggle-user').forEach(function(button) {
                button.addEventListener('click', function() {
                    const details = document.getElementById(button.dataset.target);
                    const isHidden = details.classList.contains('hidden');

                    details.classList.toggle('hidden', !isHidden);
                    button.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
                    button.querySelector('.toggle-label').textContent = isHidden ? 'Hide Details' : 'Edit / Archive';
                    button.querySelector('.toggle-icon').textContent = isHidden ? '−' : '＋';
                });
            });
        });
    </script>
</x-app-layout>