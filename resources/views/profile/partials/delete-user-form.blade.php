<div class="profile-form">

    <p class="profile-warning">
        Once your account is deleted, all of its resources and data will be permanently deleted.
        Before deleting your account, please download any data or information you wish to retain.
    </p>

    <button
        type="button"
        class="profile-danger-button"
        id="openDeleteAccountModal">
        Delete Account
    </button>

    {{-- Modal --}}
    <div class="profile-modal" id="deleteAccountModal" aria-hidden="true">
        <div class="profile-modal-backdrop" data-close-delete-modal></div>

        <div class="profile-modal-content" role="dialog" aria-modal="true" aria-labelledby="deleteAccountTitle">
            <div class="profile-modal-head">
                <h3 id="deleteAccountTitle">Delete Account</h3>
                <p>Please enter your password to confirm you would like to permanently delete your account.</p>
            </div>

            <form method="post" action="{{ route('profile.destroy') }}" class="profile-modal-body">
                @csrf
                @method('delete')

                <div class="profile-field">
                    <label for="delete_password" class="sr-only">Password</label>
                    <input
                        id="delete_password"
                        name="password"
                        type="password"
                        class="profile-input"
                        placeholder="Password">
                    @error('password', 'userDeletion')
                    <p class="profile-error">{{ $message }}</p>
                    @enderror
                </div>

                <div class="profile-modal-actions">
                    <button type="button" class="profile-secondary-button" data-close-delete-modal>Cancel</button>
                    <button type="submit" class="profile-danger-button">Delete Account</button>
                </div>
            </form>
        </div>
    </div>

</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('deleteAccountModal');
        const open = document.getElementById('openDeleteAccountModal');

        if (!modal || !open) return;

        function openModal() {
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        open.addEventListener('click', openModal);

        modal.querySelectorAll('[data-close-delete-modal]').forEach(el => {
            el.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
        });
    });
</script>