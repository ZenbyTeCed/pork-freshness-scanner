@extends('layouts.app')

@section('content')
{{-- Account settings page --}}
<div class="settings-page">
    <div class="settings-container">

        <div class="settings-header">
            <h1>Settings</h1>
            <p>Manage your account profile and password</p>
        </div>

        <div class="settings-content">

            {{-- Profile and security form --}}
            <div class="settings-section active" id="profileSection">
                <form id="profileSettingsForm">
                    <div class="settings-card">
                        <h2>Profile Information</h2>

                        <div class="form-grid profile-grid">
                            <div class="form-group">
                                <label for="fullName">Full Name</label>
                                <input type="text" id="fullName" name="name" value="{{ old('name', session('firebase_name', '')) }}" required>
                            </div>

                            <div class="form-group full-width">
                                <label for="emailAddress">Email Address</label>
                                <div class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5A2.25 2.25 0 0 1 19.5 19.5H4.5a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0L12 12.75l-9.75-6m19.5 0A2.25 2.25 0 0 0 19.5 4.5H4.5a2.25 2.25 0 0 0-2.25 2.25" />
                                    </svg>
                                    <input type="email" id="emailAddress" value="{{ session('firebase_email', '') }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="settings-card">
                        <h2>Security</h2>
                        <p id="securityProviderMessage" class="settings-note"></p>

                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="currentPassword">Current Password</label>
                                <div class="input-icon">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 0h10.5A2.25 2.25 0 0 1 19.5 12.75v6A2.25 2.25 0 0 1 17.25 21h-10.5A2.25 2.25 0 0 1 4.5 18.75v-6A2.25 2.25 0 0 1 6.75 10.5Z" />
                                    </svg>
                                    <input type="password" id="currentPassword" autocomplete="current-password" placeholder="Required only when changing password">
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="newPassword">New Password</label>
                                <input type="password" id="newPassword" autocomplete="new-password" placeholder="Leave blank to keep current password">
                            </div>

                            <div class="form-group">
                                <label for="confirmPassword">Confirm Password</label>
                                <input type="password" id="confirmPassword" autocomplete="new-password" placeholder="Repeat new password">
                            </div>
                        </div>
                    </div>

                    <p id="profileSettingsMessage" class="settings-message" role="status"></p>

                    <div class="settings-actions">
                        <button type="submit" id="profileSaveBtn" class="save-btn">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

<script type="module">
    // Handles profile updates and password changes.
    import { auth } from "{{ Vite::asset('resources/js/auth.js') }}";
    import {
        EmailAuthProvider,
        onAuthStateChanged,
        reauthenticateWithCredential,
        updatePassword,
        updateProfile
    } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

    const profileSettingsForm = document.getElementById('profileSettingsForm');
    const profileSaveBtn = document.getElementById('profileSaveBtn');
    const profileSettingsMessage = document.getElementById('profileSettingsMessage');
    const fullNameInput = document.getElementById('fullName');
    const emailAddressInput = document.getElementById('emailAddress');
    const currentPasswordInput = document.getElementById('currentPassword');
    const newPasswordInput = document.getElementById('newPassword');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const securityProviderMessage = document.getElementById('securityProviderMessage');
    const passwordInputs = [currentPasswordInput, newPasswordInput, confirmPasswordInput];

    const showToast = (message, type = 'info') => {
        let toastContainer = document.querySelector('.app-toast-container');

        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.className = 'app-toast-container';
            document.body.appendChild(toastContainer);
        }

        const toast = document.createElement('div');
        toast.className = `app-toast ${type}`;
        toast.textContent = message;
        toastContainer.appendChild(toast);

        requestAnimationFrame(() => toast.classList.add('show'));

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 220);
        }, 3200);
    };

    const showProfileMessage = (message, type = 'info') => {
        profileSettingsMessage.textContent = message;
        profileSettingsMessage.className = `settings-message ${type}`;
        showToast(message, type);
    };

    const hasPasswordProvider = (user) => {
        return user.providerData.some((provider) => provider.providerId === 'password');
    };

    const setPasswordFieldsEnabled = (enabled) => {
        passwordInputs.forEach((input) => {
            input.disabled = !enabled;

            if (!enabled) {
                input.value = '';
            }
        });

        securityProviderMessage.textContent = enabled
            ? 'Password changes are available for email/password accounts.'
            : 'This account uses Google sign-in. Password changes are managed through your Google account.';
    };

    onAuthStateChanged(auth, (user) => {
        if (!user) return;

        fullNameInput.value = user.displayName || fullNameInput.value;
        emailAddressInput.value = user.email || emailAddressInput.value;
        setPasswordFieldsEnabled(hasPasswordProvider(user));
    });

    profileSettingsForm?.addEventListener('submit', async (event) => {
        event.preventDefault();

        const user = auth.currentUser;
        if (!user) {
            showProfileMessage('Your session expired. Please log in again.', 'error');
            return;
        }

        const fullName = fullNameInput.value.trim();
        const currentPassword = currentPasswordInput.value;
        const newPassword = newPasswordInput.value;
        const confirmPassword = confirmPasswordInput.value;

        if (!fullName) {
            showProfileMessage('Full name is required.', 'error');
            return;
        }

        if (newPassword || confirmPassword) {
            if (newPassword.length < 6) {
                showProfileMessage('New password must be at least 6 characters.', 'error');
                return;
            }

            if (newPassword !== confirmPassword) {
                showProfileMessage('New password and confirmation do not match.', 'error');
                return;
            }

            if (!currentPassword) {
                showProfileMessage('Current password is required to change your password.', 'error');
                return;
            }

            if (!user.email || !hasPasswordProvider(user)) {
                showProfileMessage('Password changes are only available for email/password accounts.', 'error');
                return;
            }
        }

        profileSaveBtn.disabled = true;
        showProfileMessage('Saving changes...', 'info');

        try {
            if (newPassword) {
                const credential = EmailAuthProvider.credential(user.email, currentPassword);
                await reauthenticateWithCredential(user, credential);
            }

            if (user.displayName !== fullName) {
                await updateProfile(user, { displayName: fullName });
            }

            if (newPassword) {
                await updatePassword(user, newPassword);
            }

            const sessionResponse = await fetch("{{ route('auth.session.update') }}", {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}",
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    uid: user.uid,
                    email: user.email,
                    name: fullName
                })
            });

            if (!sessionResponse.ok) {
                throw new Error('Firebase was updated, but the app session could not be refreshed.');
            }

            currentPasswordInput.value = '';
            newPasswordInput.value = '';
            confirmPasswordInput.value = '';
            showProfileMessage('Profile settings saved.', 'success');
        } catch (error) {
            console.error(error);

            const firebaseMessages = {
                'auth/invalid-credential': 'Current password is incorrect.',
                'auth/wrong-password': 'Current password is incorrect.',
                'auth/weak-password': 'New password is too weak.',
                'auth/requires-recent-login': 'Please log in again before changing your password.'
            };

            showProfileMessage(firebaseMessages[error.code] || error.message || 'Unable to save profile settings.', 'error');
        } finally {
            profileSaveBtn.disabled = false;
        }
    });
</script>
@endsection
