$(function(){
    //baseUri
    $('head').append('<script src="js/default/baseuri.js" type="text/javascript"></script>');
    //stupidtable
    $(".table").stupidtable();
    //editar
    $('.edit').live('click',function(){
        var id = $(this).attr('id');
        var url = baseUri +'/admin/pagina/editar/'+id+'/';
        window.location = url;
    })
    //cancel
    $('.cancel').live('click',function(){
        $('#page_area').val('');
        $('#collapseOne').collapse('hide'); 
        $('#add-area').find('b').html('Cadastrar Nova Página');
        $('#f-pagina').attr('action',$('#f-pagina').attr('action').replace('/atualizar/','/incluir/'));
        $('#btn-add').html('Cadastrar');
        $('#page_title').val('');
        $('#add-area').find('.icon-edit').removeClass('icon-edit').addClass('icon-plus-sign');
        $('#page_title').removeClass('invalid');
    })
    //remove
    $('.remove').live('click',function(){
        var id = $(this).attr('id');
        $('#modal-remove').modal('show');
        var url = baseUri +'/admin/pagina/remover/'+id+'/';
        $('#btn-remove').attr('href',url);
    })      
    //event change
    $('#page_area').live('change',function(){
        if($('#page_area option:selected').val() != ""){
            $('#page_area').removeClass('invalid');            
        }
    })
})
function valida(){
    if($('#page_area option:selected').val() == ""){
        $('#page_area').addClass('invalid');
        $('#page_area').focus();
        return false;        
    }
    else{
        $('#page_area').removeClass('invalid');
    }
    if($.trim($('#page_title').val()) == ""){
        $('#page_title').addClass('invalid');
        $('#page_title').focus();
        return false;
    }
    else{
        $('#page_title').removeClass('invalid');
    }
}

