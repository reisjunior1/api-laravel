<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Conta;

class ContaTest extends TestCase
{
    /** @test */
    public function check_se_colunas_em_contas_esta_correto()
    {
        $conta = new Conta;

        $expect = [
            'numero',
        ];
        $array_comparacao = array_diff($expect, $conta->getFillable());
        //dd($array_comparacao);
        $this->assertEquals(0, count($array_comparacao));
    }

    public function check_se_dado_inserido_conta_esta_correto(){
        $this->browser;
    }
}
