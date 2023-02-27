import { API } from "@assets/js/helpers/client";
import { ROUTES } from "@assets/js/utils/routes";
import { parseError, parseResponse } from "@assets/js/helpers/client";
import Swal from "sweetalert2";

export const forgottenPasswordModal = function () {
    Swal.fire({
        title: "Réinitialisation du mot de passe",
        html: `<label for="email">Votre adresse email :</label>
            <input id="forgottenPasswordEmail" type="email" name="email"
            value="${document.getElementById("inputEmail").value}"
            class="form-control form-control-lg"
            autocomplete="off"
            placeholder="Email"
            aria-label="Email"/>`,
        buttonsStyling: false,
        customClass: {
            confirmButton: "btn btn-primary mx-1",
            cancelButton: "btn btn-danger mx-1",
        },
        showCancelButton: true,
        confirmButtonText: "Valider",
        cancelButtonText: "Annuler",
        showLoaderOnConfirm: true,
        preConfirm: () => {
            const email = document.getElementById("forgottenPasswordEmail").value;
            const token = document.getElementById("forgottenPasswordBtn").getAttribute("data-token");
            forgottenPasswordAction({ email, token });
            Swal.close();
        },
        backdrop: true,
        allowOutsideClick: () => !Swal.isLoading()
    });
};

const forgottenPasswordAction = ({ email, token }) => {
    return API.post(ROUTES.FORGOTTEN_PASSWORD, { email, token })
        .then((response) => parseResponse(response))
        .catch((error) => parseError(error));
};
