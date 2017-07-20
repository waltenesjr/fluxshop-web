 var baseUri = $('base').attr('href').replace('/app/','');  
$(function(){   
    $(window).load(function(){      
        //carrega novos produtos (ultimos cadastrados)
        var url = baseUri+'/index/FillMaisNovosVistos/2/';
        $.getJSON(url,function(data){
            $('#mais-novos .slides').html('');
            $(data).each(function(k,v){
                var img = '<img src="'+baseUri+'/thumb/t/' + v.foto_url + '/250/200/ratio"  />';
                var link = '<a href="'+baseUri+'/produto/' + v.categoria_url+'/'+v.sub_url+'/'+v.item_url+'/'+v.item_id+'/">';
                link += ' ' + v.item_short_title + '';
                link += img;
                link += '<span class="text-center">';
                if(v.item_valor_original){
                    //link += 'De <b class="line-strike">  R$ ' + v.item_valor_original + '</b>  <br >Por ';
                }
                link += '<b>  ' + v.item_preco + '</b>';
                link += '</span>';
                link +='</a>';
                $('<li />')
                .attr('id',v.item_id)
                .html(link)
                .appendTo($('#mais-novos .slides'));
            });                         
            //inicializa o slider
            $('#mais-novos').flexslider({
                animation: "slide",
                animationLoop: true,
                itemWidth: 250
            //itemMargin: 2                 
            });   
        })
        
        //carrega banner/slide lateral 270x220
        var url = baseUri+'/index/FillBanner/2';
        $.getJSON(url,function(data){
            $('#banner-left-300 .slides').html('');
            $(data).each(function(k,v){
                var img = '<img src="'+baseUri+'/thumb/slide/' + v.slide_url + '/270/220/ratio/"  width="270" height="220" alt="" />';
                if(v.slide_link != 0){
                    var link = '<a href="'+v.slide_link+'">';
                }else{
                    var link = '<a>';    
                }
                link += img;
                link +='</a>';
                $('<li />')
                .addClass('b-gray')
                .attr('id',v.slide_id)
                .html(link)
                .appendTo($('#banner-left-300 .slides') );
            });                         
            $('#banner-left-300').flexslider({
                animation: "slide",
                animationLoop: true,
                itemWidth: 270,
                itemMargin: 4              
            });   
        })
          
        //carrega banner/slide lateral 270x600
        var url = baseUri+'/index/FillBanner/3';
        $.getJSON(url,function(data){
            $('#banner-left-600 .slides').html('');
            $(data).each(function(k,v){
                var img = '<img src="'+baseUri+'/thumb/slide/' + v.slide_url + '/270/600/ratio/"  width="270" height="600" alt="" />';
                if(v.slide_link != 0){
                    var link = '<a href="'+v.slide_link+'">';
                }else{
                    var link = '<a>';    
                }
                link += img;
                link +='</a>';
                $('<li />')
                .addClass('b-gray')
                .attr('id',v.slide_id)
                .html(link)
                .appendTo($('#banner-left-600 .slides') );
            });                         
            //carrega banners bottom
            $('#banner-left-600').flexslider({
                animation: "slide",
                animationLoop: true,
                itemWidth: 270,
                itemMargin: 4              
            });   
        })
    });
    //button remove item
    $('.btn-cart-remove-home').live('click',function(){
        var id = $(this).attr('id');
        var url = baseUri+'/carrinho/remove/'+id+'/no-redirect/';
        $.post(url,{},function(data){
            $('#tr_'+id).fadeOut(600,function(){
                $('#tr_'+id).remove();  
            });    
            if(data == '' || data <= 0){
                $('#left-cart-total').html('<b>O carrinho está vazio! ;(</b>');
                 $('.cart-view').delay(500).fadeOut(700);
                 $('#cart-left').delay(700).fadeOut(500);
            }else{
                $('#total_compra').html('Total R$ ' + data);
            }
        })
    })     
});  