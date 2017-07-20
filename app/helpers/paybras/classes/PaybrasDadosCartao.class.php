<?php

class PaybrasDadosCartao {
    private $numero;
    private $parcelas;
    private $codigo_seguranca;
    private $validade_mes;
    private $validade_ano;
    private $bandeira;
    private $portador_nome;
    private $portador_cpf;
    private $portador_data_nascimento;
    private $portador_telefone_ddd;
    private $portador_telefone;

    // Inicializa nova instância da classe PaybrasDadosCartao
    public function __construct (Array $dados = null) {
        if ($dados) {
            if (isset($dados['numero'])) {
                $this->numero = $dados['numero'];
            }
            if (isset($dados['parcelas'])) {
                $this->parcelas = $dados['parcelas'];
            }
            if (isset($dados['codigo_seguranca'])) {
                $this->codigo_seguranca = $dados['codigo_seguranca'];
            }
            if (isset($dados['validade_mes'])) {
                $this->validade_mes = $dados['validade_mes'];
            }
            if (isset($dados['validade_ano'])) {
                $this->validade_ano = $dados['validade_ano'];
            }
            if (isset($dados['bandeira'])) {
                $this->bandeira = $dados['bandeira'];
            }
            if (isset($dados['portador_nome'])) {
                $this->portador_nome = $dados['portador_nome'];
            }
            if (isset($dados['portador_cpf'])) {
                $this->portador_cpf = $dados['portador_cpf'];
            }
            if (isset($dados['portador_data_de_nascimento'])) {
                $this->portador_data_nascimento = $dados['portador_data_de_nascimento'];
            }
            if (isset($dados['portador_telefone_ddd'])) {
                $this->portador_telefone_ddd = $dados['portador_telefone_ddd'];
            }
            if (isset($dados['portador_telefone'])) {
                $this->portador_telefone = $dados['portador_telefone'];
            }
        } else {
            throw new Exception("Dados do cartão não setados.");
        }
    }

    // Retorna número do cartão
    public function getNumero() {
        return !empty($this->numero) ? $this->numero : null;
    }

    // Seta número do cartão
    public function setNumero($numero) {
        $this->numero = $numero;
    }

    // Retorna número de parcelas
    public function getParcelas() {
        return !empty($this->parcelas) ? $this->parcelas : null;
    }

    // Seta número de parcelas
    public function setParcelas($parcelas) {
        $this->parcelas = $parcelas;
    }

    // Retorna codigo de seguranca
    public function getCodigoSeguranca() {
        return !empty($this->codigo_seguranca) ? $this->codigo_seguranca : null;
    }

    // Seta codigo de seguranca
    public function setCodigoSeguranca($codigo_seguranca) {
        $this->codigo_seguranca = $codigo_seguranca;
    }

    // Retorna mês do vencimento do cartão
    public function getValidadeMes() {
        return !empty($this->validade_mes) ? $this->validade_mes : null;
    }

    // Seta mês do vencimento do cartão
    public function setValidadeMes($validade_mes) {
        $this->validade_mes = $validade_mes;
    }

    // Retorna ano do vencimento do cartão
    public function getValidadeAno() {
        return !empty($this->validade_ano) ? $this->validade_ano : null;
    }

    // Seta ano do vencimento do cartão
    public function setValidadeAno($validade_ano) {
        $this->validade_ano = $validade_ano;
    }

    // Retorna bandeira do cartão
    public function getBandeira() {
        return !empty($this->bandeira) ? $this->bandeira : null;
    }

    // Seta bandeira do cartão
    public function setBandeira($bandeira) {
        $this->bandeira = $bandeira;
    }

    // Retorna nome do portador do cartão
    public function getPortadorNome() {
        return !empty($this->portador_nome) ? $this->portador_nome : null;
    }

    // Seta nome do portador do cartão
    public function setPortadorNome($portador_nome) {
        $this->portador_nome = $portador_nome;
    }

    // Retorna cpf do portador do cartão
    public function getPortadorCPF() {
        return !empty($this->portador_cpf) ? $this->portador_cpf : null;
    }

    // Seta cpf do portador do cartão
    public function setPortadorCPF($portador_cpf) {
        $this->portador_cpf = $portador_cpf;
    }

    // Retorna data de nascimento do portador do cartão
    public function getPortadorNascimento() {
        return !empty($this->portador_data_nascimento) ? $this->portador_data_nascimento : null;
    }

    // Seta data de nascimento do portador do cartão
    public function setPortadorNascimento($portador_data_nascimento) {
        $this->portador_data_nascimento = $portador_data_nascimento;
    }

    // Retorna DDD do telefone do portador do cartão
    public function getPortadorDDD() {
        return !empty($this->portador_telefone_ddd) ? $this->portador_telefone_ddd : null;
    }

    // Seta DDD do telefone do portador do cartão
    public function setPortadorDDD($portador_telefone_ddd) {
        $this->portador_telefone_ddd = $portador_telefone_ddd;
    }

    // Retorna nome do portador do cartão
    public function getPortadorTelefone() {
        return !empty($this->portador_telefone) ? $this->portador_telefone : null;
    }

    // Seta telefone do portador do cartão
    public function setPortadorTelefone($portador_telefone) {
        $this->portador_telefone = $portador_telefone;
    }
}

?>