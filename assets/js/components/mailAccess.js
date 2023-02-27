import { API } from "@assets/js/helpers/client";
import { ROUTES } from "@assets/js/utils/routes";
import { parseError, parseResponse } from "@assets/js/helpers/client";

export const mailAccess = (table) => {
    const mailAccessAction = ({ userId }) => {
        return API.post(ROUTES.MAIL_ACCESS, { userId })
            .then((response) => parseResponse(response))
            .catch((error) => parseError(error));
    };

    $(function () {
        table.on("click", ".accessEmail:not(.disabled)", function () {
            const userId = $(this).attr("data-user-id");
            mailAccessAction({ userId });
        });
    });
};
