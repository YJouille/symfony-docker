import axios from "axios";
import { HTTP_CODES } from "@assets/js/utils/httpCodes";
import { ROUTES } from "@assets/js/utils/routes";
import { addFlashMessage } from "@assets/js/components/flashMessages";
import { showLoader, hideLoader } from "@assets/js/components/loader";

export const parseResponse = ({ data }) => {
    data?.status &&
    data?.message &&
    addFlashMessage(data.status, data.message);
};

export const parseError = (error) => {
    if (process.env.APP_ENV == "dev") {
        // console.log(error);
    }

    if (error.response?.status === HTTP_CODES.UNAUTHORIZED) {
        window.location.href = ROUTES.LOGIN;
    }

    const message = (
        error.response?.data?.message ??
        error.response?.data?.detail ??
        error.response?.message ??
        "Oops.. Une erreur est survenue"
    );

    addFlashMessage("danger", message);
};

const config = {
    headers: {
        'X-Requested-With': 'XMLHttpRequest'
    },
    timeout: 300000,
};

const API = axios.create(config);
API.interceptors.request.use(
    (request) => {
        showLoader();
        return request;
    },
);
API.interceptors.response.use(
    (response) => {
        hideLoader();
        return response;
    },
    (error) => {
        hideLoader();

        if (error.response?.status === HTTP_CODES.UNAUTHORIZED) {
            window.location.href = ROUTES.LOGIN;
        }

        return Promise.reject(error);
    }
);

export { API };
