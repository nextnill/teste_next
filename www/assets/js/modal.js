var divsModal = new Array();

function showModal(div_name)
{
    divsModal.push(div_name);

    var pos_atual = divsModal.length - 1;
    
    //for (var i = 0; i < divsModal.length; i++) {
    //  alert(divsModal[i]);
    //};

    if (pos_atual > 0) {
        $('#'+divsModal[pos_atual-1]).modal('hide');
    }

    $('#'+divsModal[pos_atual]).modal({
        keyboard: false,
        backdrop: 'static'
    });

    $('#'+divsModal[pos_atual]).modal('show');
}

function closeModal(div_name, divs_hidden)
{
    //for (var i = 0; i < divsModal.length; i++) {
    //  alert(divsModal[i]);
    //};

    if (divs_hidden) {
        for (var i = 0; i < divs_hidden.length; i++) {
            for (var j = 0; j < divsModal.length; j++) {
                if (divsModal[j] == divs_hidden[i]) {
                    divsModal.splice(j, 1);
                }
            };
        };
    }

    for (var i = 0; i < divsModal.length; i++) {
        if (divsModal[i] == div_name) {
            $('#'+div_name).modal('hide');
            divsModal.splice(i, 1);

            if (divsModal.length > 0) {
                $('#'+divsModal[divsModal.length-1]).modal('show');
            }
        }
    };

    //for (var i = 0; i < divsModal.length; i++) {
    //  alert(divsModal[i]);
    //};
}