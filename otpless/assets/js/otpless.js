/**
 * Handles the OTP-less login callback.
 * @param {Object} otplessUser - The user object returned from the OTPless SDK.
 */
function otpless(otplessUser) {
    const token = otplessUser.token;
    console.log('Token:', token);
    console.log('User Details:', JSON.stringify(otplessUser));

    $('#hidden_data').val(JSON.stringify(otplessUser));

    // Submit the form with a short delay
    setTimeout(() => {
        $('#otpless_form').submit();
    }, 300);
}