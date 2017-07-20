try{
    $(function() {
        //$('.validate p span').hide();
        /* Required message */
        var requiredMsg = "Campo Requerido!";
        /* E-mail message */
        var mailMsg = "O e-mail informado � inv�lido!";
        /* CPF message */
        var cpfMsg = "CPF informado � inv�lido!";
        /* cnpj message */
        var cnpjMsg = "CNPJ informado � inv�lido!";
        /* Data message */
        var dataMsg = "Data informada � inv�lida!";    
        /* Numeric message */
        var numericMsg = "O valor deve ser n�merico!";
        /* minlength message */
        var minMsg = "Informe ao menos X caracteres!";
        /* maxlength message */
        var maxMsg = "A quantidade m�xima � de X caracteres!";
        /* Password message */
        var passwordMsg = "Senhas n�o conferem!";
        /* Telefone message */
        var foneMsg = "O telefone informado � inv�lido!";    
    
        /* mascaras */
        //$('head').append('<script src="js/jquery/jquery.mask.js" type="text/javascript"></script>');
        /* mascara data */
        $('.datar').mask('99/99/9999');
        /* mascara cpf */
        $('.cpf').mask('999.999.999-99');
        /* mascara cnpj */
        $('.cnpj').mask('99.999.999/9999-99');
        /* mascara CEP */
        $('.cep').mask('99999-999');
        /* mascara telefone */
        $('.fone').mask('(99) 9999-9999'); 
        /* mascara celularSP */
        $('.cel').mask('(00) 00009-0000'); 
        /* mascara money*/
        $('.money').mask('000.000.000.000.000,00', {reverse: true});
        /* validate style - comentar alinha abaixo para omitir o style */
        /* botao reset - limpa forms*/
        $('.reset').live('click',function(){
            $('form').attr('onsubmit','return false');
            $('form').find('*').val('');
            $('form').find('*').removeClass('invalid').removeClass('valid');
            //$('form').find('span').fadeOut(10);
            return false;
        });
        /* Aplicando Placeholder com texto do SPAN */
        $(this).find('.required').each(function(){
            //$(this).attr('placeholder',$(this).parent().find('span').html())
            });
    
        $('.submit').live('click',function(){
            $('body').find('form.validate').submit();
        })
        
        $('.validate').submit(function() {
            var valid = true;
                  
            $(this).find('.required').each(function(i){
                /* required */
                if ( $(this).hasClass('required') && $.trim( $(this).val() ) == ""){
                    $(this).removeClass('valid').addClass('invalid');
                    var elm = $(this);
                    $('html, body').animate({
                        scrollTop: $(this).offset().top - 200
                    }, 1000,function(){
                        elm.focus();                    
                        elm.parent().find('span').html(' * ' + requiredMsg);
                    });               
                    valid = false;
                    return false;
                }
                else
                {
                    $(this).removeClass('invalid').addClass('valid');
                   $(this).parent().find('span').html(' * ');
                }
		                
                /* minlength value */
                if ( $(this).attr('minlength') && $(this).hasClass('required') ){
                    if($.trim($(this).val()).length < $(this).attr('minlength') ){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.focus();                    
                            elm.parent().find('span').html(' * ' + minMsg.replace(/X/g,elm.attr('minlength')));
                        });
                        valid = false;
                        return false;
                    }
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                }

            
                /* numeric value */
                if ( $(this).hasClass('required') && $(this).hasClass('numeric') ){
                    var nan = new RegExp(/(^-?\d\d*\.\d*$)|(^-?\d\d*$)|(^-?\.\d\d*$)/);
                    if (!nan.test($.trim( $(this).val() ))){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.focus();                    
                            elm.parent().find('span').html(' * ' + numericMsg);
                        });  
                        valid = false;
                        return false;
                    }
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                }
		
                /* mail */
                if ( $(this).hasClass('email') ){
                    var er = new RegExp(/^[A-Za-z0-9_\-\.]+@[A-Za-z0-9_\-\.]{2,}\.[A-Za-z0-9]{2,}(\.[A-Za-z0-9])?/);
                    if (!er.test($.trim( $(this).val() ))){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.focus();                    
                            elm.parent().find('span').html(' * ' + mailMsg);
                        });                          
                        valid = false;
                        return false;
                    }
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                } 
            
                /* fone */
                if ( $(this).hasClass('fone') ){
                        console.log($(this).val().length )
                     if($.trim($(this).val()).length <  14 ){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.focus();                    
                            elm.parent().find('span').html(' * ' + foneMsg);
                        });                          
                        valid = false;
                        return false;
                    }
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                } 

                /* data */
                if ( $(this).hasClass('datar') ){
                
                    var sdata = $(this).val();
                    if(sdata.length!=10)
                    {
                        $(this).removeClass('valid').addClass('invalid');                        
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + dataMsg);
                        });     
                         
                        valid = false;
                        return false;   
                    }
                    var data        = sdata;
                    var dia         = data.substr(0,2);
                    var barra1      = data.substr(2,1);
                    var mes         = data.substr(3,2);
                    var barra2      = data.substr(5,1);
                    var ano         = data.substr(6,4);
                    if(data.length!=10||barra1!="/"||barra2!="/"||isNaN(dia)||isNaN(mes)||isNaN(ano)||dia>31||mes>12)
                    {
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + dataMsg);
                        });     
                        valid = false;
                        return false;            
                    }
                    if((mes==4||mes==6||mes==9||mes==11) && dia==31){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + dataMsg);
                        });     
                        valid = false;
                        return false;
                    }
                    if(mes==2 && (dia>29||(dia==29 && ano%4!=0))){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + dataMsg);
                        });  
                        valid = false;
                        return false;
                    }
                    if(ano < 1900)
                    {
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + dataMsg);
                        });  
                        valid = false;
                        return false;
                    }                
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                } 
            
                /* cpf */
                if ( $(this).hasClass('cpf') ){
                    var cpf = $(this).val().replace('.','');
                    cpf = cpf.replace('.','');
                    cpf = cpf.replace('-','');
                    while(cpf.length < 11) cpf = "0"+ cpf;
                    var expReg = /^0+$|^1+$|^2+$|^3+$|^4+$|^5+$|^6+$|^7+$|^8+$|^9+$/;
                    var a = [];
                    var b = new Number;
                    var c = 11;
                    for (i=0; i<11; i++){
                        a[i] = cpf.charAt(i);
                        if (i < 9) b += (a[i] * --c);
                    }
                    if ((x = b % 11) < 2) {
                        a[9] = 0
                    } else {
                        a[9] = 11-x
                    }
                    b = 0;
                    c = 11;
                    for (y=0; y<10; y++) b += (a[y] * c--);
                    if ((x = b % 11) < 2) {
                        a[10] = 0;
                    } else {
                        a[10] = 11-x;
                    }
                    if ((cpf.charAt(9) != a[9]) || (cpf.charAt(10) != a[10]) || cpf.match(expReg))
                    {
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + cpfMsg);
                        });  
                        valid = false;
                        return false;
                    }
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                } 
            
                /*valida cnpj*/
                if($(this).hasClass('cnpj'))
                {
                    var cnpj = $(this).val()
                    cnpj = cnpj.replace('.','');
                    cnpj = cnpj.replace('.','');
                    cnpj = cnpj.replace('/','');
                    cnpj = cnpj.replace('-','');
                    var a = new Array();
                    var b = new Number;
                    var c = [6,5,4,3,2,9,8,7,6,5,4,3,2];
                    for (i=0; i<12; i++){
                        a[i] = cnpj.charAt(i);
                        b += a[i] * c[i+1];
                    }
                    if ((x = b % 11) < 2) {
                        a[12] = 0
                    } else {
                        a[12] = 11-x
                    }
                    b = 0;
                    for (y=0; y<13; y++) {
                        b += (a[y] * c[y]);
                    }
                    if ((x = b % 11) < 2) {
                        a[13] = 0;
                    } else {
                        a[13] = 11-x;
                    }
                    if ((cnpj.charAt(12) != a[12]) || (cnpj.charAt(13) != a[13])){
                    
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + cnpjMsg);
                        });  
                        valid = false;
                        return false;
                    }
                    else
                    {
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                }
		
                /* maxlength value */
                if ( $(this).attr('maxlength')  && $(this).hasClass('required') ){
                    if($.trim($(this).val()).length > $(this).attr('maxlength') ){
                        $(this).removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            elm.select();                    
                            elm.parent().find('span').html(' * ' + maxMsg);
                        });  
                        valid = false;
                        return false;
                    }
                    else{
                        $(this).removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                }	
	
                /* password */
                if ( $(this).hasClass('password') && $(this).parent().parent().find('.password').hasClass('password')){ 
                
                    if ($.trim( $(this).val() ) != $.trim( $(this).parent().parent().find('.password').val() )){
                        $(this).parent().find('.password').removeClass('valid').addClass('invalid');
                        var elm = $(this);
                        $('html, body').animate({
                            scrollTop: $(this).offset().top - 200
                        }, 1000,function(){
                            $(this).parent().find('.password').focus();                   
                            elm.parent().find('span').html(' * ' + passwordMsg);
                        });                          
                        valid = false;
                        return false;
                    }
                    else{ 
                        $(this).nextAll('.password').removeClass('invalid').addClass('valid');
                        $(this).parent().find('.password').removeClass('invalid').addClass('valid');
                        $(this).parent().parent().find('.password').removeClass('invalid').addClass('valid');
                        $(this).parent().find('span').html(' * ');
                    }
                }

            })
            return valid;
        })
    })
}catch(err){
    alert("error in "+err.description);
}
