export let createPopupContainer = () => {
    return $("#popupContainer").dxPopup({
        showCloseButton: true,
        height: "auto",
        width: "auto",
        title: "Выберите материалы для добавления"
    }).dxPopup("instance");
}
