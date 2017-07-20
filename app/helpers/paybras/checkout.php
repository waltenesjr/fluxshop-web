<?php
global $pedido;
global $pagador;
global $entrega;
global $produtos;
?>
<html>
    <head>
        <link href="<?= HTTPURL . HELPERDIR ?>paybras/servicos/css/bootstrap.min.css" rel="stylesheet" media="screen"> <!-- Usar estilo que o lojista desejar -->
        <link rel="stylesheet" type="text/css" href="<?= HTTPURL . HELPERDIR ?>/paybras/servicos/css/style.css"> <!-- Usar estilo que o lojista desejar -->
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/jquery.js"></script>
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/bootstrap.min.js"></script> <!-- Usar estilo que o lojista desejar -->
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/jquery.mask.js"></script>
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/jquery.mask.min.js"></script>
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/jquery.validate.min.js"></script>
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/bootstrap-tooltip.js"></script>
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/bootstrap-popover.js"></script>
        <script type="text/javascript" src="<?= HTTPURL . HELPERDIR ?>paybras/servicos/js/scripts.js"></script>
    </head>

    <body>
        <div class='container'>
            <form id='formCartao' action='<?= HTTPURL ?>finalizar/paybrasAuth/' method="post" target="_parent">
                <input type='hidden' name='pagador_nome' value='<?php echo $pagador["pagador_nome"]; ?>'> <!-- Obrigatório --> <!-- Nome do cliente -->
                <input type='hidden' name='pagador_email' value='<?php echo $pagador["pagador_email"]; ?>'> <!-- Obrigatório --> <!-- Email do cliente -->
                <input type='hidden' name='pagador_cpf' value='<?php echo $pagador["pagador_cpf"]; ?>'> <!-- Obrigatório --> <!-- CPF do cliente -->
                <input type='hidden' name='pagador_rg' value='<?php echo $pagador["pagador_rg"]; ?>'> <!-- RG do cliente -->
                <input type='hidden' name='pagador_telefone_ddd' value='<?php echo $pagador["pagador_telefone_ddd"]; ?>'> <!-- Obrigatório --> <!-- DDD do cliente -->
                <input type='hidden' name='pagador_telefone' value='<?php echo $pagador["pagador_telefone"]; ?>'> <!-- Obrigatório --> <!-- Telefone do cliente -->
                <input type='hidden' name='pagador_celular_ddd' value='<?php echo $pagador["pagador_celular_ddd"]; ?>'><!-- DDD do celular do cliente -->
                <input type='hidden' name='pagador_celular' value='<?php echo $pagador["pagador_celular"]; ?>'> <!-- Celular do cliente -->
                <input type='hidden' name='pagador_sexo' value='<?php echo $pagador["pagador_sexo"]; ?>'> <!-- Sexo do cliente (M ou F) -->
                <input type='hidden' name='pagador_data_nascimento' value='<?php echo $pagador["pagador_data_nascimento"]; ?>'><!-- Dt de nascimento do cliente -->
                <input type='hidden' name='pagador_ip' value='<?php echo $_SERVER["REMOTE_ADDR"]; ?>'>


                <input type='hidden' name='entrega_nome' value='<?php echo $entrega["entrega_nome"]; ?>'> <!-- Obrigatório --> <!-- Nome para entrega -->
                <input type='hidden' name='entrega_logradouro' value='<?php echo $entrega["entrega_logradouro"]; ?>'> <!-- Obrigatório --> <!-- Endereço de entrega -->
                <input type='hidden' name='entrega_numero' value='<?php echo $entrega["entrega_numero"]; ?>'> <!-- Número do Endereço de entrega -->
                <input type='hidden' name='entrega_complemento' value='<?php echo $entrega["entrega_complemento"]; ?>'> <!-- Complemento do Endereço de entrega -->
                <input type='hidden' name='entrega_bairro' value='<?php echo $entrega["entrega_bairro"]; ?>'> <!-- Obrigatório --> <!-- Bairro de entrega -->
                <input type='hidden' name='entrega_cep' value='<?php echo $entrega["entrega_cep"]; ?>'> <!-- Obrigatório --> <!-- CEP de entrega -->
                <input type='hidden' name='entrega_cidade' value='<?php echo $entrega["entrega_cidade"]; ?>'> <!-- Obrigatório --> <!-- Cidade de entrega -->
                <input type='hidden' name='entrega_estado' value='<?php echo $entrega["entrega_estado"]; ?>'> <!-- Obrigatório --> <!-- Estado de entrega -->
                <input type='hidden' name='entrega_pais' value='<?php echo $entrega["entrega_pais"]; ?>'> <!-- Obrigatório --> <!-- PaÃ­s de entrega -->

                <input type='hidden' name='pagador_logradouro' value='<?php echo $pagador["pagador_logradouro"]; ?>'> <!-- Endereço do cliente -->
                <input type='hidden' name='pagador_numero' value='<?php echo $pagador["pagador_numero"]; ?>'> <!-- Número do Endereço do cliente -->
                <input type='hidden' name='pagador_bairro' value='<?php echo $pagador["pagador_bairro"]; ?>'> <!-- Bairro do cliente -->
                <input type='hidden' name='pagador_cep' value='<?php echo $pagador["pagador_cep"]; ?>'> <!-- CEP do cliente -->
                <input type='hidden' name='pagador_cidade' value='<?php echo $pagador["pagador_cidade"]; ?>'> <!-- Cidade do cliente -->
                <input type='hidden' name='pagador_estado' value='<?php echo $pagador["pagador_estado"]; ?>'> <!-- Estado do cliente -->
                <input type='hidden' name='pagador_pais' value='<?php echo $pagador["pagador_pais"]; ?>'> <!-- PaÃ­s do cliente -->

                <input type='hidden' name='pedido_id' value='<?php echo $pedido["pedido_id"]; ?>'> <!-- Obrigatório --> <!-- ID do pedido (armazenado na loja) -->
                <input type='hidden' name='pedido_valor_total_original' value='<?php echo $pedido["pedido_valor_total_original"]; ?>'> <!-- Obrigatório --> <!-- Valor da Compra (produtos + frete) -->
                <input type='hidden' name='pedido_descricao' value='<?php echo $pedido["pedido_descricao"]; ?>'> <!-- Descrição do pedido -->
                <input type='hidden' name='pedido_moeda' value='BRL'> <!-- Aceito apenas Real --> <!-- Obrigatório -->
                <input type='hidden' name='pedido_url_redirecionamento' value='<?php echo $pedido["pedido_url_redirecionamento"]; ?>'> <!-- Obrigatório para TEF --> <!-- URL do retorno após pagamento com dÃ©bito em conta -->
                <input type='hidden' name='pedido_meio_pagamento' id='meio_pagamento'> <!-- Setado via JS ao escolher as formas de pgto inseridas nesse formulÃ¡rio --> <!-- Obrigatório -->
                <?php
                if ( isset( $produtos ) && !empty( $produtos ) )
                {
                    foreach ( $produtos as $key => $value )
                    {
                        ?>
                        <input type='hidden' name='produtos[<?php echo $key; ?>][produto_codigo]' value='<?php echo $value['produto_codigo']; ?>'> <!-- Produto-->
                        <input type='hidden' name='produtos[<?php echo $key; ?>][produto_nome]' value='<?php echo $value['produto_nome']; ?>'> <!-- Código do Produto -->
                        <input type='hidden' name='produtos[<?php echo $key; ?>][produto_categoria]' value='<?php echo $value['produto_categoria']; ?>'> <!-- Categoria do Produto -->
                        <input type='hidden' name='produtos[<?php echo $key; ?>][produto_qtd]' value='<?php echo $value['produto_qtd']; ?>'> <!-- Quantidade do Produto -->
                        <input type='hidden' name='produtos[<?php echo $key; ?>][produto_valor]' value='<?php echo $value['produto_valor']; ?>'> <!-- Valor do Produto -->
                        <input type='hidden' name='produtos[<?php echo $key; ?>][produto_peso]' value='<?php echo $value['produto_peso']; ?>'> <!-- Peso do Produto -->
                    <?php }
                } ?>

                <?php
                if ( isset( $_POST['recebedores'] ) && !empty( $_POST['recebedores'] ) )
                {
                    foreach ( $_POST['recebedores'] as $key => $value )
                    {
                        ?>
                        <input type='hidden' name='recebedores[<?php echo $key; ?>][recebedor_email]' value='<?php echo $value['recebedor_email']; ?>'> <!-- Email do recebedor secundÃ¡rio-->
                        <input type='hidden' name='recebedores[<?php echo $key; ?>][recebedor_valor]' value='<?php echo $value['recebedor_valor']; ?>'> <!-- Valor atribuido ao recebedor secundÃ¡rio -->
                    <?php }
                } ?>

                <h5><strong>Escolha a Forma de Pagamento</strong></h5>
                <table class="table table-striped">
                    <tr>
                        <td><strong>Cartão de Crédito</strong></td>
                        <td class='card'>
                            <label class='visa' title='Visa'>
                                <input type="radio" id='bandeira_visa' name="paymentOpt" value="visa">
                            </label>
                        </td>
                        <td class='card'>
                            <label class='master' title='MasterCard'>
                                <input type="radio" id='bandeira_master' name="paymentOpt" value="mastercard">
                            </label>
                        </td>
                        <td class='card'>
                            <label class='amex' title='American Express'>
                                <input type="radio" id='bandeira_amex' name="paymentOpt" value="amex">
                            </label>
                        </td>
                        <td class='card'>
                            <label class='diners' title='Diners Club'>
                                <input type="radio" id='bandeira_diners' name="paymentOpt" value="diners">
                            </label>
                        </td>
                        <td class='card'>
                            <label class='elo' title='Elo'>
                                <input type="radio" id='bandeira_elo' name="paymentOpt" value="elo">
                            </label>
                        </td>
                    </tr>
                    <tr class="hide">
                        <td><strong>Débito On-Line</strong></td>
                        <td class='debt' colspan='4'>
                            <label class='bb' title='Banco do Brasil'>
                                <input type="radio" id='bb_radio' name="paymentOpt">
                            </label>
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <td><strong>Boleto Bancário</strong></td>
                        <td class='bill'>
                            <label class='boleto' title='Boleto Bancário'>
                                <input type="radio" id='boleto_radio' name="paymentOpt" >
                            </label>
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>   
                </table>

                <div id="card" class="payMethod hide">
                    <div class='card span9'>
                        <h5 style='float:left'><strong>Pagamento via Cartão de Crédito</strong></h5>
                        <label id='label_card' style='margin-left: 580px;'></label>
                        <hr>
                    </div>
                    <div class='span4 mleft70'>
                        <div class="control-group">
                            <label class='tituloInput'>Nº do Cartão</label>
                            <input class="width200 cardMask" type="text" name="cartao_numero" id="cartao_numero" autocomplete="off"/>
                        </div>

                        <div class="control-group">
                            <label class='tituloInput'>Código de Segurança</label>
                            <input class="width50 secCodeMask" type="text" id='codigo_seguranca' name="cartao_codigo_de_seguranca"/>
                            <a id="cartao_codigo_de_seguranca" rel="popover" data-html="true" data-title='Código de Segurança' data-content="
                               <html>
                               <div class='span6' style='font-size: 12px; margin-bottom: 15px;'>
                               <p>O código de segurança é uma sequência numérica complementar ao número do cartão de crédito que assegura a veracidade dos dados de uma transação eletrônica.</p>
                               </div>
                               <div class='row'>
                               <div class='span3' style='float: left;'>
                               <img src='<?= HTTPURL . HELPERDIR ?>paybras/servicos/img/codigo_cartao.png'></img>
                               <p style='margin-top: 10px; font-size: 13px; font-weight:bold;'>Para os Cartões Visa, Marcatercard, Dineers e Elo:</p>
                               <p style='font-size: 12px; margin-left: 10px;'>Informar os três números localizados no verso do cartão.</p>
                               </div>
                               <div class='span3' style='float: left;'>
                               <img src='<?= HTTPURL . HELPERDIR ?>paybras/servicos/img/codigo_amex.png'></img>
                               <p style='margin-top: 10px; font-size: 13px; font-weight:bold;'>Para os Cartões American Express:</p>
                               <p style='font-size: 12px; margin-left: 10px;'>Informar os quatro números localizados na frente do cartão</p>
                               </div>
                               </div>
                               </html>">
                                <i class='icon-question-sign'></i>
                            </a>
                        </div>

                        <div class="control-group">
                            <label class='tituloInput'>Validade</label>
                            <select class="select2-input select2-focused width60" name="cartao_validade_mes">
                                <option value=""></option>
                                <option value="01">01</option>
                                <option value="02">02</option>
                                <option value="03">03</option>
                                <option value="04">04</option>
                                <option value="05">05</option>
                                <option value="06">06</option>
                                <option value="07">07</option>
                                <option value="08">08</option>
                                <option value="09">09</option>
                                <option value="10">10</option>
                                <option value="11">11</option>
                                <option value="12">12</option>
                            </select>
                            <select class="select2-input select2-focused width75" name="cartao_validade_ano" id="cartao_validade_ano">
                                <option value=""></option>
                                <?php for ( $i = 0; $i < 10; $i++ )
                                { ?>
                                    <option value="<?php echo date( 'y' ) + $i; ?>"><?php echo date( 'Y' ) + $i; ?></option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="control-group">
                            <label class='tituloInput'>Nome do Titular</label>
                            <input class="width200" type="text" name="cartao_portador_nome" id="cartao_portador_nome"/>
                        </div>

                        <div class="control-group">
                            <label class='tituloInput'>CPF do Titular</label>
                            <input class="width140 cpfMask" type="text" id="cartao_portador_cpf" name="cartao_portador_cpf"/>
                        </div>

                        <div class="control-group">
                            <label class='tituloInput'>Telefone do Titular</label>
                            <input class="width40 ddMask" type="text" name="cartao_portador_telefone_ddd"/>
                            <input class="width90 phoneMask" type="text" name="cartao_portador_telefone"/>
                        </div>
                    </div>
                    <div class='span3'>
                        <!-- Trata retorno de consulta de parcelas -->
                        <?php
                        require_once HELPERDIR . "/paybras/servicos/PaybrasPostParcelas.php";
                        $pedido_valor = $pedido['pedido_valor_total_original']; //Valor da Compra (produtos + frete)
                        $parcelas = PaybrasPostParcelas::main( $pedido_valor );
                        if ( $parcelas['sucesso'] )
                        {
                            echo "<div id='cartao_parcelas' class='control-group'>";
                            echo "  <label class='tituloInput'>Forma de parcelamento*</label>";
                            echo "  <table id='parcelas' class='table table-condensed'>";
                            foreach ( $parcelas as $key => $value )
                            {
                                if ( $key == '1' )
                                {
                                    echo "<tr><td><label class='radio'><input type='radio' checked name='cartao_parcelas' value='" . $key . "' />" . $value['parcela'] . " x R$ " . number_format( $value['valor_parcela'], 2, ',', '.' ) . "</label></td></tr>";
                                }
                                elseif ( $key != 'sucesso' )
                                {
                                    echo "<tr><td><label class='radio'><input type='radio' name='cartao_parcelas' value='" . $key . "' />" . $value['parcela'] . " x R$ " . number_format( $value['valor_parcela'], 2, ',', '.' ) . "</label></td></tr>";
                                }
                            }
                            echo "  </table>";
                            echo "</div>";
                        }
                        else
                        {
                            echo "<div class='span3 alert alert-error'>";
                            echo "  <strong >Erro na Consulta de Parcelas:</strong><br>";
                            if ( $parcelas['messagem_erro'] )
                            {
                                foreach ( $parcelas['messagem_erro'] as $value )
                                {
                                    echo "</br><p>" . $value . "</p>";
                                    $i++;
                                }
                            }
                            else
                            {
                                echo $parcelas;
                            }
                            echo "</div>";
                        }
                        ?>
                    </div>

                    <div class="span11" align='center'>
                        <hr>
                        <button id='submit_cartao' class="btn btn-primary submit">Finalizar Compra</button>
                    </div>
                </div>

                <div id="debt" class="payMethod hide">
                    <div class='debt span9'>
                        <h5 style='float:left'><strong>Pagamento via Débito On-Line</strong></h5>
                        <label id='label_debt' style='margin-left: 580px;'></label>

                        <table>
                            <tr>
                                <td><p class='pgto_text'>Esta opção está disponível apenas para clientes do <b><span id='banco'></span> que tenham acesso ao InternetBank</b></p></td>
                            </tr>
                        </table>
                    </div>
                    <div class="span11" align='center'>
                        <br /><br />
                        <hr>
                        <button id='tef' class="btn btn-primary submit">Finalizar Compra</button>
                    </div>
                </div>

                <div id="bill" class="payMethod hide">
                    <div class='bill span9'>
                        <h5 style='float:left'><strong>Pagamento via Boleto</strong></h5>
                        <label id='label_boleto' style='margin-left: 580px;'></label>

                        <table>
                            <tr>
                                <td><p class='pgto_text'>Sua compra será confirmado somente após o pagamento do boleto. O boleto não é enviado pelo correio, imprima-o e pague-o no banco ou pelo internet banking. </p></td>
                            </tr>
                        </table>
                    </div>
                    <div class="span11" align='center'>
                        <br /><br />
                        <hr>
                        <button id='boleto' class="btn btn-primary submit">Finalizar Compra</button>
                    </div>
                </div>
            </form>
        </div>
        <script>
            $('.submit').on('click',function(){
                $(this).html('Por favor aguarde...');
                $(this).attr('disabled','disabled');
                 $("#formCartao").submit();
            })
        </script>
    </body>
</html>