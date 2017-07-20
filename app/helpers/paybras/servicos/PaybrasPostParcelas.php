

<?php
    require_once HELPERDIR."/paybras/PaybrasBiblioteca.php";
    class PaybrasPostParcelas {
        public static function main($pedido_valor) {
            try {
                
                /*
                * #### Lojista ##### 
                * Você deve adicionar seus dados ao arquivo de configuraç�o (config/PaybrasConfig.php)
                */
                $dados['lojista'] = PaybrasConfig::getDadosLojista();
                $dados['conexao'] = new PaybrasDadosConexao(PaybrasConfig::getURL('parcelas'));
                $dados['pedido_valor_total'] = $pedido_valor;
                $consultaParcelas = new PaybrasConsultaParcelas($dados);                
                return self::criaPaginaParcelas($consultaParcelas->getArrayParcelas());
                
            } catch (PaybrasExcecao $e) {
                die($e->getMessage());
            }
        }

        public static function criaPaginaParcelas(Array $consultaParcelas) {
            if(isset($consultaParcelas) && !empty($consultaParcelas)) {
                return $consultaParcelas;
            } else {
                return "Erro na solicita��o de consulta de parcelas";
            }
        }
    }
?>