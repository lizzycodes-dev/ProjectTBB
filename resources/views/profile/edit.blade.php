<x-app-layout>

    <div class="profile-page">

        {{-- ========================================================= --}}
        {{-- HEADER --}}
        {{-- ========================================================= --}}
        <header class="profile-header">
            <div>
                <h1>My Profile</h1>
                <p>Manage your account information, password, and security.</p>
            </div>
        </header>


        {{-- ========================================================= --}}
        {{-- SECTIONS --}}
        {{-- ========================================================= --}}
        <div class="profile-grid">

            {{-- Update Profile Information --}}
            <section class="profile-card">
                <div class="profile-card-head">
                    <div>
                        <h2>Profile Information</h2>
                        <p>Update your name and email address.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </section>


            {{-- Update Password --}}
            <section class="profile-card">
                <div class="profile-card-head">
                    <div>
                        <h2>Update Password</h2>
                        <p>Use a long, random password to keep your account secure.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </section>


            {{-- Delete Account --}}
            <section class="profile-card profile-card-danger">
                <div class="profile-card-head">
                    <div>
                        <h2>Delete Account</h2>
                        <p>Once your account is deleted, all of its resources and data will be permanently removed.</p>
                    </div>
                </div>

                <div class="profile-card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </section>

        </div>

    </div>

</x-app-layout>