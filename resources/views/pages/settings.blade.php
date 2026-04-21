@extends('layouts.app')

@section('content')
<div class="settings-page">
    <div class="settings-container">

        <div class="settings-header">
            <h1>Settings</h1>
            <p>Manage your account and system preferences</p>
        </div>

        <div class="settings-layout">

            <div class="settings-sidebar">
                <button type="button" class="settings-link active" data-target="profileSection">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6.75a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.5 20.25a7.5 7.5 0 0 1 15 0" />
                    </svg>
                    <span>Profile</span>
                </button>

                <button type="button" class="settings-link" data-target="systemSection">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.25 5.625h-1.5A2.25 2.25 0 0 0 1.5 7.875v10.5a2.25 2.25 0 0 0 2.25 2.25h16.5a2.25 2.25 0 0 0 2.25-2.25V7.875a2.25 2.25 0 0 0-2.25-2.25h-1.5a2.31 2.31 0 0 1-1.577-.55l-1.298-1.24A2.25 2.25 0 0 0 14.323 3h-4.646a2.25 2.25 0 0 0-1.552.635l-1.298 1.24z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 11.25a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" />
                    </svg>
                    <span>System & Devices</span>
                </button>
            </div>

            <div class="settings-content">

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

                <div class="settings-section" id="systemSection">
                    <div class="settings-card">
                        <h2>ESP32-CAM Configuration</h2>

                        <div class="device-status">
                            <div class="status-left">
                                <span class="status-dot"></span>
                                <div>
                                    <p class="device-name">ESP32-CAM #1</p>
                                    <span class="device-ip">192.168.1.100</span>
                                </div>
                            </div>

                            <span class="status-badge">Connected</span>
                        </div>

                        <div class="form-group">
                            <label for="deviceIp">Device IP Address</label>
                            <input type="text" id="deviceIp" value="192.168.1.100">
                        </div>

                        <div class="form-group">
                            <label for="imageResolution">Image Resolution</label>
                            <select id="imageResolution">
                                <option>1600x1200 (UXGA)</option>
                                <option>1024x768 (XGA)</option>
                                <option>800x600 (SVGA)</option>
                            </select>
                        </div>

                        <div class="toggle-group">
                            <div>
                                <h4>Auto-capture</h4>
                                <p>Automatically capture at intervals</p>
                            </div>

                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="settings-actions">
                        <button type="button" class="save-btn">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 3.75H6.75A2.25 2.25 0 0 0 4.5 6v12a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V6.75L16.5 3.75Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 3.75V8.25H9V3.75M9 15h6" />
                            </svg>
                            <span>Save Configuration</span>
                        </button>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

<script type="module">
    import { auth } from "{{ Vite::asset('resources/js/auth.js') }}";
    import {
        EmailAuthProvider,
        onAuthStateChanged,
        reauthenticateWithCredential,
        updatePassword,
        updateProfile
    } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-auth.js";

    const settingsLinks = document.querySelectorAll('.settings-link');
    const settingsSections = document.querySelectorAll('.settings-section');
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

    settingsLinks.forEach(link => {
        link.addEventListener('click', function () {
            const targetId = this.getAttribute('data-target');

            settingsLinks.forEach(item => item.classList.remove('active'));
            settingsSections.forEach(section => section.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(targetId).classList.add('active');
        });
    });

    const showProfileMessage = (message, type = 'info') => {
        profileSettingsMessage.textContent = message;
        profileSettingsMessage.className = `settings-message ${type}`;
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
