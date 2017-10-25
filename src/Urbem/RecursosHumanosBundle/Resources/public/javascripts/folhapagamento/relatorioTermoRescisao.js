(function ($, urbem) {
    'use strict';

    var ano = urbem.giveMeBackMyField('ano'),
        mes = urbem.giveMeBackMyField('mes'),
        tipo = urbem.giveMeBackMyField('tipo'),
        lotacao = urbem.giveMeBackMyField('lotacao'),
        local = urbem.giveMeBackMyField('local'),
        funcao = urbem.giveMeBackMyField('funcao'),
        ordenacao = urbem.giveMeBackMyField('ordenacao'),
        matricula = urbem.giveMeBackMyField('matricula'),
        gerarRelatorio = urbem.giveMeBackMyField('gerarRelatorio'),
        padrao = urbem.giveMeBackMyField('padrao'),
        geral = urbem.giveMeBackMyField('geral');

    var selectTipo = function (tipo) {
        switch (tipo) {
            case "matricula":
                return matricula.select2('val');
            case "lotacao":
                return lotacao.select2('val');
            case "local":
                return local.select2('val');
            case "funcao":
                return funcao.select2('val');
            case "geral":
                return "geral";
        }
    };

    mes = urbem.giveMeBackMyField('mes');

    ano.on('change', function() {
        if ($(this).val() != '') {
            abreModal('Carregando','Aguarde, buscando compentencias...');
            $.ajax({
                url: '/api-search-competencia-pagamento/preencher-competencia-folha-pagamento',
                method: "POST",
                data: {
                    ano: $(this).val()
                },
                dataType: "json",
                success: function (data) {
                    urbem.populateSelect(mes, data, {value: 'id', label: 'label'}, mes.data('mes'));
                    fechaModal();
                }
            });
        }
    });

    $('#sonata-ba-field-container-'+UrbemSonata.uniqId+'_gerarRelatorio').hide();


    $('#gerarRelatorio').on('click', function () {
        var error = false;
        var mensagem = '';
        jQuery('.sonata-ba-field-error-messages').remove();
        jQuery('.sonata-ba-form').parent().find('.alert.alert-danger.alert-dismissable').remove();

        if ((tipo.val() == "")) {
            UrbemSonata.setFieldErrorMessage('','Campo obrigatório!', tipo.parent());
            return
        }

        if ((ano.val() == "")) {
            UrbemSonata.setFieldErrorMessage('','Campo obrigatório!', ano.parent());
            return
        }

        if ((mes.val() == "")) {
            UrbemSonata.setFieldErrorMessage('','Campo obrigatório!', mes.parent());
            return
        }

        var campoDeBusca = urbem.giveMeBackMyField(tipo.val());

        if (tipo.val() != 'geral') {
            if (campoDeBusca.select2('val') == ''  ) {
                UrbemSonata.setFieldErrorMessage('','Campo obrigatório!', campoDeBusca.parent());
                return
            }
        }

        abreModal('Carregando','Aguarde ...');
        var data = {
            'tipo' : tipo.val(),
            'tipoValor' : selectTipo(tipo.val()),
            'mes' : mes.val(),
            'ano' : ano.val(),
            'ordenacao' : ordenacao.val()
        };

        $.ajax({
            url: '/recursos-humanos/folha-pagamento/relatorios/termo-rescisao/gerar_relatorio',
            method: "GET",
            data: data,
            dataType: "json",
            success: function (data) {
                var url = '/recursos-humanos/folha-pagamento/relatorios/termo-rescisao/view-download-arquivo/';
                window.location = url + data.filename + '?total=' + data.contratos;
                fechaModal();
            },
            error: function (data) {
                fechaModal();
            }
        });
    });

})(jQuery, UrbemSonata);
