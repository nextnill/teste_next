// faz posicionar a modal no topo sempre que for exibida
$('.modal').on('shown.bs.modal', function () {
    $('.modal').scrollTop(0);
});