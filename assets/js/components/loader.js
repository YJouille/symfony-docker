export const initLoader = () => {
    $(document)
        .ajaxStart(function () {
            $("#loader").fadeIn();
        })
        .ajaxStop(function () {
            $("#loader").fadeOut();
        });
}

export const showLoader = () => {
    $("#loader").fadeIn();
}

export const hideLoader = () => {
    $("#loader").fadeOut();
}
