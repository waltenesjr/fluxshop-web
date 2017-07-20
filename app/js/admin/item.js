$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    
    $('.money').mask('000.000.000.000.000,00', {reverse: true});
    $('.price').mask('000.000.000.000.000,00', {reverse: true});
    $('#item_desconto').mask('000.000.000.000.000,00', {reverse: true});
    $('#item_preco').mask('000.000.000.000.000,00', {reverse: true});
    
    //stupidtable
    $(".table").stupidtable();   
    //button submit
    $('#btn-add').live('click',function(){
        $('#f-item').submit();
    })
    //editar item
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        window.location = baseUri+'/admin/item/editar/'+id+'/';
    })
    //cancel
    $('.cancel').live('click',function(){
        $('#sub_categoria').val('');
        $('#collapseOne').collapse('hide'); 
        $('#add-categoria').find('b').html('Cadastrar Nova Subcategoria');
        $('#f-categoria').attr('action',$('#f-categoria').attr('action').replace('/atualizar/','/incluir/'));
        $('#btn-add').html('Cadastrar');
        $('#sub_title').val('');
        $('#add-categoria').find('.icon-edit').removeClass('icon-edit').addClass('icon-plus-sign');
        $('#sub_title').removeClass('invalid');
    })
    //remover item
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/item/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })      
    //event change
    $('<option>').val('').text('Antes selecione uma categoria').appendTo('#item_sub');
    $('#item_sub').attr('disabled','disabled');
    $('#item_categoria').live('change',function(){
        if($('#item_categoria option:selected').val() != ""){
            $('<option>').val('').text('carregando subcategorias...').appendTo('#item_sub');
            var cat = $('#item_categoria option:selected').val();
            var url = baseUri+'/admin/item/fillSubCategoria/'+cat+'/'
            $.getJSON(url, {
                cat:cat
            }, 
            function(data){
                $('#item_sub option').remove();
                $('#item_sub').removeAttr('disabled');
                if(data != 0){
                    $(data.rs).each(function (k,v) {
                        $('<option>').val(v.sub_id).text(v.sub_title).appendTo('#item_sub'); 
                    })
                }
                else{
                    $('<option>').val('').text('Nenhuma subcategoria cadastrada').appendTo('#item_sub');
                    $('#item_sub').attr('disabled','disabled');
                }
            })
        }
        else{
            $('#item_sub option').remove();
            $('<option>').val('').text('Antes selecione uma categoria').appendTo('#item_sub');
            $('#item_sub').attr('disabled','disabled');
        }
    })
    //mensagens input tips
    var popcontent = '<p>Para uma melhor exibição, cadastre apenas o título do item. Exemplo:<br /> <b> Smartphone Motorola Atrix 3G</b>';
    popcontent +='<br/> Deixe os demais detalhes para os campos apropriados!</p>';
    $('#item_title').popover({
        placement:'right',
        title:'Título do Item',
        html: true, 
        content:popcontent
    });
    var popcontent2 = '<p>Separe as palavras por vírgula. Exemplo:<br /> <b> 3G, Wi-Fi, LCD, LED, PRETO...</b><p>';
    $('#item_keywords').popover({
        placement:'right',
        title:'Palavras-chave para buscas',
        html: true, 
        content:popcontent2
    });
    var popcontent3 = '<p>Esta opção determina se o Item aparecerá ou não no site.<p>';
    $('#item_show').popover({
        placement:'right',
        title:'Item Ativo',
        html: true, 
        content:popcontent3
    });
    var popcontent4 = '<p>Itens em Oferta aparecem com uma tarja diferenciada de oferta.<p>';
    $('#item_oferta').popover({
        placement:'right',
        title:'Item em Oferta',
        html: true, 
        content:popcontent4
    });
    var popcontent5 = '<p>Recomendado quando as dimensões forem maiores que 500x400 pixels.<p>';
    $('#item_zoom').popover({
        placement:'right',
        title:'Zoom nas Fotos',
        html: true, 
        content:popcontent5
    });
    var popcontentItem = '<p>Se o valor for 0,00 exibe "sob consulta" no item.<p>';
    $('#item_preco').popover({
        placement:'right',
        title:'Valor e Sob Consulta',
        html: true, 
        content:popcontentItem
    });
    
    //load sub categoria item editar
    loadSub = function (cat) {
        var url = baseUri+'/admin/item/fillSubCategoria/'+cat+'/'
        $.getJSON(url, {
            cat:cat
        }, 
        function(data){
            $('#item_sub option').remove();
            $('#item_sub').removeAttr('disabled');
            if(data != 0){
                $(data.rs).each(function (k,v) {
                    $('<option>').val(v.sub_id).text(v.sub_title).appendTo('#item_sub'); 
                })
            }
            else{
                $('<option>').val('').text('Nenhuma subcategoria cadastrada').appendTo('#item_sub');
                $('#item_sub').attr('disabled','disabled');
            }
        })    
    }

    //make sortable
    $( "#photo-gallery-ul" ).sortable({
        opacity: 0.8,
        placeholder: "ui-state-highlight",
        cursor: "move",
        stop: function(){
            var sorted = $(this).sortable('serialize');
            var url = baseUri+'/admin/item/updateFotoPos/'
            $.post(url,{
                item:sorted
            },function(data){
                console.log(data)
                notify('<h1>Nova posição gravada</h1>');
            })
        }
    });
    //make selectable
    $( "#photo-gallery-ul" ).selectable({
        start: function() {
        },
        stop: function() {
        }
    });
    //remove foto
    $('#btn-remove-foto').live('click',function(e){                    
        e.preventDefault();
        if($( "#photo-gallery-ul .ui-selected" ).length >= 1){
            $( "#photo-gallery-ul .ui-selected" ).each(function() {
                if($(this).attr('id')){   
                    var foto_id = $(this).attr('id').replace('li_','');
                    var url = baseUri+'/admin/item/removeUniqFoto/'+foto_id+'/'
                    $(this).remove();
                    //$(this).effect('clip',function(){});
                    $.post(url,{
                        foto_id:foto_id
                    },function(data){
                        //oculta controles
                        if( ( $( '#photo-gallery-ul li' ).length ) <= 0){
                           $('#foto-control').hide();
                        }    
                        
                    })                    
                }
            });   
            var sorted = $("#photo-gallery-ul").sortable('serialize');
            var url = baseUri+'/admin/item/updateFotoPos/'
            $.post(url,{
                item:sorted
            },function(data){
                //console.log(data)
                })            
        }
        else{
            notify('<h1>Nenhuma foto selecionada!</h1>');
        }
    })
    //cancela selecao
    $('#btn-remove-cancel').live('click',function(e){                    
        e.preventDefault();
        $( "#photo-gallery-ul .ui-selected" ).each(function() {
            if($(this).attr('id')){
                $(this).removeClass('ui-selected');
            }
        });    
        notify('<h1>Seleçao cancelada</h1>');
    })
    //seleciona todas 
    $('#btn-remove-all').live('click',function(e){                    
        e.preventDefault();
        $( "#photo-gallery-ul li" ).each(function() {
            if($(this).attr('id')){
                $(this).addClass('ui-selected');
            }
        });                    
        notify('<h1>Todas as fotos selecionadas</h1>');
    })
    //seleciona um com duplo clique
    $( "#photo-gallery-ul li" ).live('dblclick',function(){
        //console.log( $(this).attr('id').replace('li_','')  );
        if($(this).hasClass('ui-selected')){
            $(this).removeClass('ui-selected');
        //$(this).tooltip('hide');
        //$(this).removeData('tooltip');
        //$(this).attr('title','duplo clique para selecionar');                        
        }
        else{
            $(this).addClass('ui-selected');
        //$(this).tooltip('hide');
        //$(this).removeData('tooltip');
        //$(this).attr('title','duplo clique para desmarcar');
        //$(this).tooltip({placement:'top'});
        }
    })
    //disable selection selectable
    $( "ul,li" ).disableSelection();  
    //oculta controles
    if( $( '#photo-gallery-ul li' ).length <= 0){
        $('#foto-control').hide();
    }  
    
    $('.addAttr').live('change',function(){
        var iattr_id = $(this).attr('id');
        var iattr_qtde = $.trim( $(this).val() );
        var atributo_id = $(this).attr('att');
        var item_id = $.trim( $('#item_id').val() );
        var url = baseUri + '/admin/item/addAttr/';
        $.post(url,{
            iattr_id: iattr_id,
            item_id: item_id,
            iattr_qtde: iattr_qtde,
            atributo_id:atributo_id
        },function(data){
            if(data != 'nope'){
                notify('<h1>'+data+'!</h1>');
            }
        })
    })

    var popcontent = '<p>Largura do Item (mínimo 11cm e máximo 105cm)</p>';
    $('#item_largura').popover({
        placement:'right',
        title:'Largura do Produto',
        html: true, 
        content:popcontent
    });            
            
    var popcontent = '<p>Comprimento do Item (mínimo 16cm e máximo 105cm)</p>';
    $('#item_comprimento').popover({
        placement:'right',
        title:'Comprimento do Produto',
        html: true, 
        content:popcontent
    });  
            
    var popcontent = '<p>Altura do Item (mínimo 2cm e máximo 105cm)</p>';
    $('#item_altura').popover({
        placement:'right',
        title:'Comprimento do Produto',
        html: true, 
        content:popcontent
    });              
})
function valida(){

    if($.trim($('#item_title').val()) == ""){
        $('#item_title').addClass('invalid');
        $('#myTab a[href="#dados"]').tab('show');   
        $('#item_title').focus();
        return false;
    }
    else{
        $('#item_title').removeClass('invalid');
    }    
    if($('#item_categoria option:selected').val() == ""){
        $('#item_categoria').addClass('invalid');
        $('#myTab a[href="#dados"]').tab('show');   
        $('#item_categoria').focus();
        return false;        
    }
    else{
        $('#item_categoria').removeClass('invalid');
    }
    if($('#item_sub option:selected').val() == ""){
        $('#item_sub').addClass('invalid');
        $('#myTab a[href="#dados"]').tab('show');   
        $('#item_sub').focus();
        return false;        
    }
    else{
        $('#item_sub').removeClass('invalid');
    }
    if($.trim($('#item_preco').val()) == ""){
        $('#item_preco').addClass('invalid');
        $('#myTab a[href="#dados"]').tab('show');   
        $('#item_preco').focus();
        return false;
    }
    else{
        $('#item_preco').removeClass('invalid');
    }   
    
    if($('#item_calcula_frete option:selected').val() == "2"){
        
        if($('#item_largura').val() < 11 || $('#item_largura').val() > 105){
            $('#myTab a[href="#frete"]').tab('show');   
            $('#item_largura').addClass('invalid');
            $('#item_largura').focus();
            return false;
        }else{
            $('#item_largura').removeClass('invalid'); 
        }
            
        if($('#item_comprimento').val() < 16 || $('#item_comprimento').val() > 105){
            $('#myTab a[href="#frete"]').tab('show');   
            $('#item_comprimento').addClass('invalid');
            $('#item_comprimento').focus();
            return false;
        }else{
            $('#item_comprimento').removeClass('invalid'); 
        }
        
        if($('#item_altura').val() < 2 || $('#item_altura').val() > 105){
            $('#myTab a[href="#frete"]').tab('show');   
            $('#item_altura').addClass('invalid');
            $('#item_altura').focus();
            return false;
        }else{
            $('#item_altura').removeClass('invalid');
        }
    }    
    
//$('#myTab a[href="#desc"]').tab('show');   
}

//reload binds
function reloadFotoBind() { 
    //oculta controles
    if( $( "#photo-gallery-ul li" ).length <= 0){
        $('#foto-control').hide();
    }        
    if( $( "#photo-gallery-ul li" ).length >= 1){
        $('#foto-control').show();
    }
}

function goTo(url) {
    window.location = url;
}