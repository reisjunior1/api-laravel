<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Conta;
use App\Models\Saldo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use function PHPUnit\Framework\isNull;

class contaApiController extends Controller
{
    public function obter_moedas()
    {
        $response = Http::get('https://olinda.bcb.gov.br/olinda/servico/PTAX/versao/v1/odata/Moedas?$top=100&$format=json&$select=simbolo');

        $m = $response->json();

        //numero de campos value
        $tam = count($m['value']);

        for ($i = 0; $i <= $tam - 1; $i++) {
            DB::table('moedas')->insert([
                'simbolo' => $m['value'][$i]['simbolo'],
            ]);
        }
        return response()->json('moedas adcionadas no banco de dados');
    }
    public function insere_brl()
    {
        DB::table('moedas')->insert([
            'simbolo' => 'BRL',
        ]);
        return response()->json('moeda adicinada');
    }

    public function cotacao()
    {
        //$hoje = date('m-d-Y');
        $data = date('m-d-Y',strtotime("-1 days"));

        //conta o numero de linhas da tabela moeda
        $tam = DB::table('moedas')->count();

        //selecionar o simbolo a cada passagem do loop e concaternar na url
        for ($i = 1; $i <= $tam; $i++) {
            $sim = DB::table('moedas')
                ->select('simbolo')
                ->where('id', '=', $i)
                //->where('simbolo', '!=', (string)'BRL')
                ->first();
            
            $s = ($sim->simbolo);
            if ($sim->simbolo != 'BRL') {
                $response = Http::get("https://olinda.bcb.gov.br/olinda/servico/PTAX/versao/v1/odata/CotacaoMoedaDia(moeda=@moeda,dataCotacao=@dataCotacao)?@moeda='" . $s . "'&@dataCotacao='" . $data. "'&\$top=5&\$skip=4&\$format=json&\$select=paridadeCompra,paridadeVenda");

                $m = $response->json();
                //dd($m['value']);
                $c = ($m['value']['0']['paridadeCompra']);
                $v = ($m['value']['0']['paridadeVenda']);

                DB::table('moedas')->where('simbolo', '=', $s)->update([
                    //'m_simbolo' => $s,
                    'compra' => $c,
                    'venda' => $v,

                ]);
            }
        }
        return response()->json('taxas de compra e venda obtidas');
    }

    public function criar_conta(Request $req)
    {
        //verificação dado inserido
        if (!is_numeric($req->get('numero'))) {
            return response()->json('dado invalido');
        }
        if ($req->get('numero') >= 10000 or $req->get('numero') <= 0) {
            return response()->json('dado invalido');
        }
        $length = Str::length($req->get('numero'));
        if ($length > 4) {
            return response()->json('dado invalido');
        }

        //verifica se a conta já existe no banco de dados
        if (DB::table('contas')->where('numero', $req->get('numero'))->count() == 0) {
            $dados = $req->all();
            Conta::create($dados);

            return response()->json('Conta ' . $req->get('numero') . ' criada');
        } else {
            return response()->json('esta conta ja existe');
        }
    }

    public function deposito(Request $req)
    {
        //verificação dado inserido
        if (!is_numeric($req->get('saldo'))) {
            return response()->json('dado invalido');
        }
        if ($req->get('saldo') <= 0) {
            return response()->json('dado invalido');
        }
        if (DB::table('contas')->where('numero', $req->get('c_numero'))->count() == 0) {
            return response()->json('esta conta nao existe');
        }
        if (DB::table('moedas')->where('simbolo', $req->get('m_simbolo'))->count() == 0) {
            return response()->json('esta moeda nao existe');
        }

        $dados = $req->all();
        //se conta não possui saldo registrado, inserir dados
        if (
            DB::table('saldos')->where('c_numero', $req->get('c_numero'))->count() == 0
        ) {
            DB::table('saldos')->insert([
                'c_numero' => $req->get('c_numero'),
                'm_simbolo' => $req->get('m_simbolo'),
                'saldo' => $req->get('saldo'),
            ]);
            return response()->json($dados);
        } else {
            //se não, obtem o saldo da conta
            $valor = DB::table('saldos')
                ->select('saldo')
                ->where('c_numero', '=', $req->get('c_numero'))
                ->where('m_simbolo', '=', $req->get('m_simbolo'))
                ->first();

            //se já existir registro na moeda, atualizar o valor
            if (!is_null($valor)) {
                DB::table('saldos')
                    ->where('c_numero', '=', $req->get('c_numero'))
                    ->where('m_simbolo', '=', $req->get('m_simbolo'))
                    ->increment('saldo', $req->get('saldo'));

                return response()->json($req->get('m_simbolo') . ' ' . $req->get('saldo') . ' depositado na conta ' . $req->get('c_numero'));
            } else {
                //se não, inserir registro na conta e moeda passados
                DB::table('saldos')->insert([
                    'c_numero' => $req->get('c_numero'),
                    'm_simbolo' => $req->get('m_simbolo'),
                    'saldo' => $req->get('saldo'),
                ]);
                return response()->json($req->get('m_simbolo') . ' ' . $req->get('saldo') . ' depositado na conta ' . $req->get('c_numero'));
            }
        }
    }
    public function sacar(Request $req)
    {
        //verificação dado inserido
        if (!is_numeric($req->get('saldo'))) {
            return response()->json('dado invalido');
        }
        if ($req->get('saldo') <= 0) {
            return response()->json('dado invalido');
        }
        if (DB::table('contas')->where('numero', $req->get('c_numero'))->count() == 0) {
            return response()->json('esta conta nao existe');
        }
        if (DB::table('moedas')->where('simbolo', $req->get('m_simbolo'))->count() == 0) {
            return response()->json('esta moeda nao existe');
        }
        //obter valor e verificar se o valor a ser sacado e menor do que o existente
        $valor = DB::table('saldos')
            ->select('saldo')
            ->where('c_numero', '=', $req->get('c_numero'))
            ->where('m_simbolo', '=', $req->get('m_simbolo'))
            ->first();

        //Verificar se existe deposito na conta na moeda selecionada
        if (!is_null($valor)) {
            if ($req->get('saldo') <= $valor->saldo) {
                DB::table('saldos')
                    ->where('c_numero', '=', $req->get('c_numero'))
                    ->where('m_simbolo', '=', $req->get('m_simbolo'))
                    ->decrement('saldo', $req->get('saldo'));

                return response()->json($req->get('m_simbolo') . ' ' . $req->get('saldo') . ' sacado da conta ' . $req->get('c_numero'));
            } else {
                return response()->json('saldo insuficiente');
            }
        } else {
            return response()->json('nao ha deposito nesta moeda');
        }
    }

    public function ver_saldo(Request $req)
    {
        //dd($req->query);
        //verificação dado inserido
        if (DB::table('contas')->where('numero', $req->get('c_numero'))->count() == 0) {
            return response()->json('esta conta nao existe');
        }
        //dd($req);

        //verifica se moeda foi passado
        if (!is_null($req->m_simbolo)) {
            //verifica se moeda existe no banco de dados
            if (DB::table('moedas')->where('simbolo', $req->get('m_simbolo'))->count() == 0) {
                return response()->json('esta moeda não existe');
            }

            //seleciona o simbolo e o saldo da conta na moeda passada
            $saldo = DB::table('saldos')
                ->select('m_simbolo', 'saldo')
                ->where('c_numero', '=', $req->get('c_numero'))
                ->where('m_simbolo', '=', $req->get('m_simbolo'))
                ->first();

            $soma = 0;

            //se moeda for real
            // converter para real usando taxa venda
            if ($req->get('m_simbolo') == 'BRL') {
                //return response()->json('BRL');
                $saldo = DB::table('saldos')
                    ->select('saldo')
                    ->where('c_numero', '=', $req->get('c_numero'))
                    ->where('m_simbolo', '=', 'BRL')
                    ->first();
                //dd($saldo);
                $soma = $soma + $saldo->saldo;

                $tam = DB::table('saldos')->count();
                for ($i = 1; $i <= $tam; $i++) {
                    //selecionar saldo e simbolos da conta em outras moedas
                    $sel = DB::table('saldos')
                        ->select('saldo', 'm_simbolo')
                        ->where('id', '=', $i)
                        ->where('c_numero', '=', $req->get('c_numero'))
                        ->where('m_simbolo', '!=', $req->get('c_numero'))
                        ->first();
            
                    
                    //selecionar taxa venda das moedas 
                    $vend = DB::table('moedas')
                        ->select('venda')
                        ->where('simbolo', '=', $sel->m_simbolo)
                        //->where('m_simbolo', '!=', 'BRL')
                        ->first();

                    $conv = $sel->saldo * $vend->venda;
                    $soma = $soma + $conv;
                }
                return response()->json('saldo em ' . $req->get('m_simbolo') . ' ' . $soma);
            } else {
               // return response()->json('Nao BRL ');
                // converter pra real
                // converter para a moeda
                $saldo = DB::table('saldos')
                    ->select('saldo')
                    ->where('c_numero', '=', $req->get('c_numero'))
                    ->where('m_simbolo', '=', $req->get('m_simbolo'))
                    ->first();

                $soma = $soma + $saldo->saldo;
                //dd($soma);
                $tam = DB::table('saldos')->count();
                //dd($tam);
                for ($i = 1; $i <= $tam; $i++) {
                    $sel = DB::table('saldos')
                        ->select('saldo', 'm_simbolo')
                        ->where('id', '=', $i)
                        ->where('c_numero', '=', $req->get('c_numero'))
                        ->where('m_simbolo', '!=', $req->get('m_simbolo'))
                        ->first();

                }
                //dd($sel);
                $vend = DB::table('moedas')
                    ->select('compra', 'venda')
                    ->where('simbolo', '=', $sel->m_simbolo)
                    //->where('m_simbolo', '!=', 'BRL')
                    ->first();

                $conv = $sel->saldo * $vend->compra;
                $conv2 = $conv * $vend->venda;
                $soma = $soma + $conv2;

                return response()->json('saldo em ' . $req->get('m_simbolo') . ' ' . $soma);
            }
        } else {
            $saldo = DB::table('saldos')
                ->select('m_simbolo', 'saldo')
                ->where('c_numero', '=', $req->get('c_numero'))
                ->get();
            return response()->json($saldo);
        }
    }
}
