<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DefontanaFacturaService
{
    /**
     * Sincroniza facturas y notas de crédito de Meribia a Defontana
     */
    public function syncFacturas(): array
    {
        
    }

    /**
     * Mapea los datos de una factura Meribia al formato SaveSale de Defontana
     */
    public function mapPayloadToSaveSale(array $sol, array $cliente = []): array
    {
        // Mapping principal basado en moduleSaveSale.php
        $payload = [
            'documentType'        => 'FVAELECT',
            'serie'               => $sol['documentType'] ?? '',
            'ano'                 => $sol['ANO'] ?? '',
            'numero'              => $sol['CODIGO'] ?? '',
            'base'                => $sol['BASE'] ?? '',
            'firstFolio'          => $sol['CODIGO'] ?? '',
            'emissionDate'        => date('Y-m-d', strtotime($sol['emissionDate'] ?? 'now')),
            'firstFeePaid'        => date('Y-m-d', strtotime($sol['firstFeePaid'] ?? 'now')),
            'externalDocumentID'  => '',
            'clientFile'          => $sol['clientFile'] ?? '',
            'contactIndex'        => $cliente['address'] ?? '',
            'paymentCondition'    => $sol['paymentCondition'] ?? '',
            'sellerFileId'        => 'VENDEDOR',
            'clientAnalysis'      => [
                'accountNumber'   => $sol['CUEVEN'] ?? '',
                'businessCenter'  => '',
                'classifier01'    => '',
                'classifier02'    => '',
            ],
            'billingCoin'         => 'PESO',
            'billingRate'         => 1.0,
            'shopId'              => 'Local',
            'priceList'           => '1',
            'giro'                => $cliente['business'] ?? '',
            'city'                => $cliente['city'] ?? '',
            'district'            => $cliente['district'] ?? '',
            'isTransferDocument'  => false,
            'contact'             => -1,
            'storage'             => [
                'code'            => '',
                'motive'          => '',
                'storageAnalysis' => [
                    'accountNumber'   => '',
                    'businessCenter'  => '',
                    'classifier01'    => '',
                    'classifier02'    => '',
                ],
            ],
            'saleTaxes'           => [
                [
                    'code'        => 'IVA',
                    'value'       => (double)($sol['IVA'] ?? 0),
                    'taxeAnalysis'=> [ 'accountNumber' => '2120301001', 'businessCenter' => '', 'classifier01' => '', 'classifier02' => '' ],
                ]
            ],
            'attachedDocuments'   => [],
            'details'             => [],
            'ventaRecDesGlobal'   => [],
            'customFields'        => [],
            'gloss'               => '',
        ];

        // Documentos adjuntos (OC)
        if (!empty($sol['folioOC'])) {
            $payload['attachedDocuments'][] = [
                'date'            => date('Y-m-d', strtotime($sol['fechaOC'] ?? 'now')),
                'documentTypeId'  => '801',
                'folio'           => $sol['folioOC'],
                'reason'          => $sol['motivoOC'] ?? '',
            ];
        }

        // Detalles (productos y servicios)
        if (!empty($sol['detalles'])) {
            foreach ($sol['detalles'] as $detalle) {
                $payload['details'][] = [
                    'type'              => 'S',
                    'code'              => $detalle['codigo'],
                    'count'             => 1,
                    'productName'       => $detalle['servicio'],
                    'productNameBarCode'=> '',
                    'price'             => $detalle['precio'],
                    'unit'              => 'UN',
                    'analysis'          => [
                        'accountNumber'   => $detalle['cuenta'] ?? '3110101001',
                        'businessCenter'  => $detalle['centro_negocio'] ?? '',
                        'classifier01'    => '',
                        'classifier02'    => '',
                    ],
                ];
            }
        }

        return $payload;
    }
}
