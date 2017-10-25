var CONFIG_INSCRICAO = (function() {
    var private = {
        'INPUT': '#' + UrbemSonata.uniqId + '_',
        'WRAP_FORM': '#sonata-ba-field-container-' +  UrbemSonata.uniqId + '_',
        'URL_ASSUNTO': '/tributario/divida-ativa/inscricao/buscar-assunto/',
        'URL_PROCESSO': '/tributario/divida-ativa/inscricao/buscar-processo/',
        'EMITIR_DOCUMENTO': 'emitirDocumento'
    };
    return {
        get: function(name) { return private[name]; }
    };
})();

var factoryObjeto = function (id, value) {
    var retorno = jQuery(CONFIG_INSCRICAO.get(id));
    if (value) {
        retorno = jQuery(CONFIG_INSCRICAO.get(id) + value);
    }
    return retorno;
};


var sucesso = function (data, tag, selected) {
    var tagName = tag.prop("tagName");
    if (tagName == "SELECT") {
        tag.empty().append("<option value=\"\">Selecione</option>").select2("val", "").val('').trigger("change");
        $.each(data, function (index, value) {
            if (index == selected) {
                tag
                    .append("<option value=" + index + " selected>" + value + "</option>");
            } else {
                tag
                    .append("<option value=" + index + ">" + value + "</option>");
            }
        });
        tag.prop('disabled', false);
        tag.select2();
    }

    if (tagName == "INPUT") {
        var type = tag.prop("type");
        if (type == "text") {
            tag.val(data);
        }
    }
};

var ajaxSelect = function (url, tag, selected) {
    jQuery.ajax({
        url: url,
        method: "GET",
        dataType: "json",
        success: function (data) {
            sucesso(data.data, tag, selected);
        }
    });
};