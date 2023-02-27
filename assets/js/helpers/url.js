export const getUrlParameter = (sParam) => {
    const sPageURL = window.location.search.substring(1);
    const sURLVariables = sPageURL.split("&");
    let sParameterName = null;
    let result = null;

    for (let i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split("=");

        if (sParameterName[0] === sParam) {
            result = sParameterName[1] === undefined ? null : decodeURIComponent(sParameterName[1]);
        }
    }

    return result;
};
