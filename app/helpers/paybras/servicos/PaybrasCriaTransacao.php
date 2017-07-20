<?php 
require_once HELPERDIR."/paybras/PaybrasBiblioteca.php";?>

<?php
class PaybrasCriaTransacao {

    public static function main($dados_post) {
        try {
            
            /*
            * #### Lojista ##### 
            * Você deve adicionar seus dados ao arquivo de configuração (config/PaybrasConfig.php)
            */
            $dados['lojista'] = PaybrasConfig::getDadosLojista();
            $dados['conexao'] = new PaybrasDadosConexao(PaybrasConfig::getURL('criacao'));

            // Passa para array de dados os dados do cartão, recuperados do POST, apartir do método dadosCartao
            // Obrigatório apenas para pagamento com cartão de crédito
            $dados['cartao'] = self::dadosCartao($dados_post);

            // Passa para array de dados os dados do pagador, recuperados do POST, apartir do método dadosPagador
            $dados['pagador'] = self::dadosPagador($dados_post);

            // Passa para array de dados os dados do pedido, recuperados do POST, apartir do método dadosPedido
            $dados['pedido'] = self::dadosPedido($dados_post);

            // Passa para array de dados os dados do endereço de entrega, recuperados do POST, apartir do método dadosEnderecoEntrega
            $dados['endereco_entrega'] = self::dadosEnderecoEntrega($dados_post);

            // Passa para array de dados os dados do produto, recuperados do POST, apartir do método dadosProduto
            // Não obrigatório
            $dados['produtos'] = self::dadosProduto($dados_post);

            // Passa para array de dados os dados do recebedor secundário, recuperados do POST, apartir do método dadosRecebedor
            // Não obrigatório
            $dados['recebedor_secundario'] = self::dadosRecebedor($dados_post);
            
            $criacaoTransacao = new PaybrasCriacaoTransacao($dados);

            return self::retornoCriacaoTransacao($criacaoTransacao->getArrayCriacaoTransacao());
            
        } catch (PaybrasExcecao $e) {
            die($e->getMessage());
        }
        
    }

    public static function retornoCriacaoTransacao(Array $retornoTransacao) {
        if(isset($retornoTransacao) && !empty($retornoTransacao)) {
            return $retornoTransacao;
        } else {
            //Caso a transação não tenha sido enviada a API do Paybras
            echo "Erro no envio da transação";
        }
    }

    private function dadosCartao($dados_post) {
        if($dados_post['pedido_meio_pagamento'] == 'cartao'){
            $dados['numero'] = $dados_post['cartao_numero'];
            $dados['parcelas'] = $dados_post['cartao_parcelas'];
            $dados['codigo_seguranca'] = $dados_post['cartao_codigo_de_seguranca'];
            $dados['bandeira'] = $dados_post['paymentOpt'];
            $dados['portador_nome'] = $dados_post['cartao_portador_nome'];
            $dados['validade_mes'] = $dados_post['cartao_validade_mes'];
            $dados['validade_ano'] = $dados_post['cartao_validade_ano'];
            $dados['portador_cpf'] = $dados_post['cartao_portador_cpf'];
            $dados['portador_data_nascimento'] = isset($dados_post['cartao_portador_data_de_nascimento']) ? $dados_post['cartao_portador_data_de_nascimento'] : null;
            $dados['portador_telefone_ddd'] = isset($dados_post['cartao_portador_telefone_ddd']) ? $dados_post['cartao_portador_telefone_ddd'] : null;
            $dados['portador_telefone'] = isset($dados_post['cartao_portador_telefone']) ? $dados_post['cartao_portador_telefone'] : null;

            return new PaybrasDadosCartao($dados);

        } else {
            return null;
        }
    }

    private function dadosPagador($dados_post) {

        if(isset($dados_post['pagador_nome']) || isset($dados_post['pagador_email']) || isset($dados_post['pagador_cpf']) || isset($pagador_end['logradouro'])){

            $pagador_end['logradouro'] = $dados_post['pagador_logradouro'];
            $pagador_end['numero'] = isset($dados_post['pagador_numero']) ? $dados_post['pagador_numero'] : null;
            $pagador_end['complemento'] = isset($dados_post['pagador_complemento']) ? $dados_post['pagador_complemento'] : null;
            $pagador_end['bairro'] = $dados_post['pagador_bairro'];
            $pagador_end['cep'] = $dados_post['pagador_cep'];
            $pagador_end['cidade'] = $dados_post['pagador_cidade'];
            $pagador_end['estado'] = $dados_post['pagador_estado'];
            $pagador_end['pais'] = $dados_post['pagador_pais'];

            $pagador['nome'] = $dados_post['pagador_nome'];
            $pagador['email'] = $dados_post['pagador_email'];
            $pagador['cpf'] = $dados_post['pagador_cpf'];
            $pagador['rg'] = isset($dados_post['pagador_rg']) ? $dados_post['pagador_rg'] : null;
            $pagador['telefone_ddd'] = $dados_post['pagador_telefone_ddd'];
            $pagador['telefone'] = $dados_post['pagador_telefone'];
            $pagador['celular_ddd'] = isset($dados_post['pagador_celular_ddd']) ? $dados_post['pagador_celular_ddd'] : null;
            $pagador['celular'] = isset($dados_post['pagador_celular']) ? $dados_post['pagador_celular'] : null;
            $pagador['sexo'] = isset($dados_post['pagador_sexo']) ? $dados_post['pagador_sexo'] : null;
            $pagador['data_nascimento'] = isset($dados_post['pagador_data_nascimento']) ? $dados_post['pagador_data_nascimento'] : null;
            $pagador['ip'] = isset($dados_post['pagador_ip']) ? $dados_post['pagador_ip'] : null;

            $pagador['endereco'] = new PaybrasDadosEndereco($pagador_end);

            return new PaybrasDadosPagador($pagador);

        } else {
            throw new Exception("Dados do comprador são obrigatórios.");
        }
    }

    private function dadosPedido($dados_post) {

        if(isset($dados_post['pedido_id']) || isset($dados_post['pedido_url_redirecionamento']) || isset($dados_post['pedido_meio_pagamento']) || isset($dados_post['pedido_moeda']) || isset($dados_post['pedido_valor_total_original'])){

            $dados['id'] = $dados_post['pedido_id'];
            $dados['descricao'] = isset($dados_post['pedido_descricao']) ? $dados_post['pedido_descricao'] : null;
            $dados['meio_pagamento'] = $dados_post['pedido_meio_pagamento'];
            $dados['moeda'] = $dados_post['pedido_moeda'];
            $dados['valor_total_original'] = $dados_post['pedido_valor_total_original'];
            $dados['url_redirecionamento'] = 'http://www.taoff.com.br';

            return new PaybrasDadosPedido($dados);

        } else {
            return null;
        }
    }

    private function dadosEnderecoEntrega($dados_post) {

        if(!empty($dados_post['entrega_nome'])){
            $entrega_end['logradouro'] = $dados_post['entrega_logradouro'];
            $entrega_end['numero'] = isset($dados_post['entrega_numero']) ? $dados_post['entrega_numero'] : null;
            $entrega_end['complemento'] = isset($dados_post['entrega_complemento']) ? $dados_post['entrega_complemento'] : null;
            $entrega_end['bairro'] = $dados_post['entrega_bairro'];
            $entrega_end['cep'] = $dados_post['entrega_cep'];
            $entrega_end['cidade'] = $dados_post['entrega_cidade'];
            $entrega_end['estado'] = $dados_post['entrega_estado'];
            $entrega_end['pais'] = $dados_post['entrega_pais'];
            $entrega['endereco'] = new PaybrasDadosEndereco($entrega_end);
            $entrega['nome'] = $dados_post['entrega_nome'];

        } else {
            $entrega_end['logradouro'] = $dados_post['pagador_logradouro'];
            $entrega_end['numero'] = isset($dados_post['pagador_numero']) ? $dados_post['pagador_numero'] : null;
            $entrega_end['complemento'] = isset($dados_post['pagador_complemento']) ? $dados_post['pagador_complemento'] : null;
            $entrega_end['bairro'] = $dados_post['pagador_bairro'];
            $entrega_end['cep'] = $dados_post['pagador_cep'];
            $entrega_end['cidade'] = $dados_post['pagador_cidade'];
            $entrega_end['estado'] = $dados_post['pagador_estado'];
            $entrega_end['pais'] = $dados_post['pagador_pais'];
            $entrega['endereco'] = new PaybrasDadosEndereco($entrega_end);
            $entrega['nome'] = $dados_post['pagador_nome'];
        }
        
        return new PaybrasDadosEntrega($entrega);
    }

    private function dadosProduto($dados_post) {
        if(isset($dados_post['produtos']) && !empty($dados_post['produtos'])){
            $dados = array();

            foreach ($dados_post['produtos'] as $value) {
                $dados['nome'] = $value['produto_nome'];
                $dados['codigo'] = $value['produto_codigo'];
                $dados['categoria'] = $value['produto_categoria'];
                $dados['qtd'] = $value['produto_qtd'];
                $dados['valor'] = $value['produto_valor'];
                $dados['peso'] = $value['produto_peso'];

                $produtos[] = new PaybrasDadosProduto($dados);
                $dados = null;
            }
            return $produtos;
        } else {
            return null;
        }
    }

    private function dadosRecebedor($dados_post) {
        if(isset($dados_post['recebedores']) && !empty($dados_post['recebedores'])){
            $dados = array();

            foreach ($dados_post['recebedores'] as $value) {
                $dados['email'] = $value['recebedor_email'];
                $dados['valor'] = $value['recebedor_valor'];

                $recebedores[] = new PaybrasDadosRecebedorSecundario($dados);
                $dados = null;
            }
            return $recebedores;
        } else {
            return null;
        }
    }
}
?>