import { forgottenPasswordModal } from "@assets/js/components/sweetAlert2";

$(function () {
    $("#forgottenPasswordBtn").on("click", function (event) {
        event.preventDefault();
        forgottenPasswordModal();
    });
});
