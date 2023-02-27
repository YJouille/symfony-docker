export const initFlashMessages = () => {
    $(".flash_message").each(function (index, element) {
        initFlashMessage($(element).data("id"), $(element).data("type"));
    });
}

export const addFlashMessage = (type, message) => {
    let id = $(".flash_message").length + 1;

    $("#flash_message_container").append(`
        <div id="flash_message_${type}_${id}"
            class="flash_message toast"
            role="alert"
            aria-live="assertive"
            aria-atomic="true"
            data-type="${type}"
            data-id="${id}"
            data-bs-autohide="true"
            data-bs-delay="5000"
        >
            <div class="toast-header border-0 bg-${type}">
                <i class="fa-regular fa-bell"></i>
                <span class="me-auto font-weight-bold">${frontFlashMessages[type]}</span>
                <i class="fas fa-times text-md ms-3 cursor-pointer" data-bs-dismiss="toast" aria-label="Close"></i>
            </div>
            <hr class="horizontal dark m-0">
            <div class="toast-body">
                ${message}
            </div>
        </div>
    `);

    initFlashMessage(id, type);
};

export const initFlashMessage = (id, type) => {
    const element = document.getElementById(`flash_message_${type}_${id}`);
    new bootstrap.Toast(element);
    bootstrap.Toast.getInstance(element).show();

    $(element).on("hidden.bs.toast", function () {
        $("#flash_message_container").remove(element);
    });
};
