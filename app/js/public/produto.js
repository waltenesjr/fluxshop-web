$(function(){
    var baseUri = $('base').attr('href').replace('/app/','');       
    if( $("#mycarousel img").length >= 4){
        $("#mycarousel").carouFredSel({
            circular: false,
            infinite: false,
            auto: false,
            prev: {	
                button: "#mycarousel_prev",
                key: "left"
            },
            next:{ 
                button: "#mycarousel_next",
                key: "right"
            },
            scroll:{
                items :4
            }
            ,
            pagination: "#mycarousel_pag"
        });
    }else{
        $('.next').remove();
        $('.prev').remove();
    }    
    //fancybox fotos
    $(".rel").fancybox({
        openEffect : 'elastic',
        openSpeed  : 150,
        closeEffect : 'elastic',
        closeSpeed  : 350, 
        arrows: true,
        maxWidth: 900,
        maxHeight: 700,
        helpers : {
            thumbs: {
                width  : 60,
                height : 60,
                source  : function(current) {
                    return $(current.element).data('thumbnail');
                }
            }            
        }                
    }); 
    //preload                
    $('.zoomer img').each(function(){
        var path = $(this).attr('data');
        var img = document.createElement('img');
        img.src = path;        
    });  
    //configura zoom
    $('.zoomer').live('mouseover',function(e){
        e.preventDefault();
        e.stopPropagation();              
        var id = $(this).find('img').attr('id');        
        var zoomIn = $(this).attr('zoom');    
        if($('#zoom img').attr('id'))
        {
            if($('#zoom img').attr('id').replace('b_','') == id)
                return false;
        }
        var small = $('#'+id).attr('src').replace('74/70/','280/280/ratio');
        var big = $('#'+id).attr('data');
        $('.jqzoom').remove();
        $('<a />')
        .attr('href',big)
        .attr('title','')
        .attr('data',small)
        .attr('data-fancybox-group','gallery')
        .attr('data-thumbnail',small)
        .attr('id','f_'+id)
        .addClass('jqzoom')
        .addClass('rel')
        .appendTo(".zoom");       
        $('.jqzoom').attr('href',big)
        $('<img />')
        .attr('src',small)
        .attr('id','b_'+id)
        .attr('data',big)
        .attr('title','')
        .css('margin-left','0px')
        .css('padding','0px !important')
        .appendTo('.jqzoom');
        
        if(zoomIn == 1){
            $('.jqzoom').jqzoom({
                //zoomType: 'reverse',
                lens:true
                ,
                preloadImages: false
                //,alwaysOn:false
                ,
                zoomWidth: 605
                ,
                zoomHeight: 360
            }); 
        }
        $('.jqzoom').live('click',function(e){
            e.preventDefault();
            $('.zoomer').each(function(){
                $(this).attr('href',$(this).attr('data'));
            })
        })
    })      
    //start zoom 
    $('.zoomer img').first().mouseover();    
    $('.attr_sel').live('change',function(){
        var attid = $(this).attr('group');
        $('#'+attid).val($(this).val())
    })
    //adicionar ao carrinho
    $('.addtocart').live('click',function(e){
        e.preventDefault();
        var item_id = $(this).attr('id');
        var attr_selecteds = true;
        $(".attr_sel").each(function(){
            if($(this).val() == ""){
                addPop($(this).attr('id'),'' + $(this).attr('desc') ,'Selecione uma op&#231;&#227;o para continuar.','top' );
                attr_selecteds = false;
                return false;
                $('#attr-alert').hide();
                $('#attr-alert').addClass('alert');
                $('#attr-alert').addClass('alert-error');
                $('#attr-alert').html('Selecione uma op&#231;&#227;o de '  +   $(this).attr('desc') + ' para continuar.');
                $('#attr-alert').fadeIn();
                attr_selecteds = false;
                return false;
            }
        })
        if(attr_selecteds == false){
            return false;
        }        
        var attr_data = $('#fattr').serializeArray();  
        var url = baseUri+'/carrinho/adicionar/'+item_id+'/'
        $.post(url,{
            attr:attr_data,
            id:item_id
        },function(data){
            window.location = baseUri+'/carrinho/'
        })
    })      
})

function addPop(elm,title,msg,pos) {
    var content = msg;
    $('.sel_'+elm).popover({
        placement:pos,
        title:title,
        html: true, 
        content:content
    });
    var popover = $('.sel_'+elm).data('popover');
    popover.options.content = content;
    $('.sel_'+elm).popover('show');     
}