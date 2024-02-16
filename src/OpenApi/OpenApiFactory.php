<?php
namespace App\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\OpenApi;
use ApiPlatform\OpenApi\Model;

class OpenApiFactory implements OpenApiFactoryInterface
{

    public function __construct(private OpenApiFactoryInterface $decorated)
    {
    }

    public function __invoke(array $context = []): OpenApi
    {
        $openApi = $this->decorated->__invoke($context);

        $schemas = $openApi->getComponents()->getSchemas();

 
        $schemas['Token'] = new \ArrayObject([
            'type' => 'object',
            'properties' => [
                'token' => [
                    'type' => 'string',
                    'readOnly' => true,
                ],
            ],
        ]);
        $schemas['Credentials'] = new \ArrayObject([
            'type' => 'object',
                'properties' => [
                'username' => [
                    'type' => 'string',
                    'example' => 'admin@bookapi.com',
                ],
                'password' => [
                    'type' => 'string',
                    'example' => 'password',
                ],
            ],
        ]);
 
        $pathItem = new Model\PathItem(
            ref: 'JWT Token',
            post: new Model\Operation(
                operationId: 'postCredentialsItem',
                tags: ['Token'],
                responses: [
                    '200' => [
                        'description' => 'Get JWT token',
                        'content' => [
                            'application/json' => [
                                'schema' => [
                                         '$ref' => '#/components/schemas/Token',
                                ],
                            ],
                        ],
                    ],
                ],
                summary: 'Get JWT token to login.',
                requestBody: new Model\RequestBody(
                    description: 'Generate new JWT Token',
                    content: new \ArrayObject([
                        'application/json' => [
                            'schema' => [
                                         '$ref' => '#/components/schemas/Credentials',
                            ],
                        ],
                    ]),
                ),
            ),
        );
        $openApi->getPaths()->addPath('/api/login_check', $pathItem);

        // $pathItem = $openApi->getPaths()->getPath('/api/login_check');
        // $operation = $pathItem->getGet();

        // $openApi->getPaths()->addPath('/api/login_check', $pathItem->withGet(
        //     $operation->withParameters(array_merge(
        //         $operation->getParameters(),
        //         [new Model\Parameter('fields', 'query', 'Fields to remove of the output')]
        //     ))
        // ));

        // $openApi = $openApi->withInfo((new Model\Info('New Title', 'v2', 'Description of my custom API'))->withExtensionProperty('info-key', 'Info value'));
        // $openApi = $openApi->withExtensionProperty('key', 'Custom x-key value');
        // $openApi = $openApi->withExtensionProperty('x-value', 'Custom x-value value');

        // // to define base path URL
        // $openApi = $openApi->withServers([new Model\Server('https://foo.bar')]);

        return $openApi;
    }
}

// final class JwtDecorator implements OpenApiFactoryInterface
// {

 
//     public function __invoke(array $context = []): OpenApi
//     {
//         $openApi = ($this->decorated)($context);
//         $schemas = $openApi->getComponents()->getSchemas();

 
//         $schemas['Token'] = new \ArrayObject([
//             'type' => 'object',
//             'properties' => [
//                 'token' => [
//                     'type' => 'string',
//                     'readOnly' => true,
//                 ],
//             ],
//         ]);
//         $schemas['Credentials'] = new \ArrayObject([
//             'type' => 'object',
//                 'properties' => [
//                 'username' => [
//                     'type' => 'string',
//                     'example' => 'admin@bookapi.com',
//                 ],
//                 'password' => [
//                     'type' => 'string',
//                     'example' => 'password',
//                 ],
//             ],
//         ]);
 
//         $pathItem = new Model\PathItem(
//             ref: 'JWT Token',
//             post: new Model\Operation(
//                 operationId: 'postCredentialsItem',
//                 tags: ['Token'],
//                 responses: [
//                     '200' => [
//                         'description' => 'Get JWT token',
//                         'content' => [
//                             'application/json' => [
//                                 'schema' => [
//                                          '$ref' => '#/components/schemas/Token',
//                                 ],
//                             ],
//                         ],
//                     ],
//                 ],
//                 summary: 'Get JWT token to login.',
//                 requestBody: new Model\RequestBody(
//                     description: 'Generate new JWT Token',
//                     content: new \ArrayObject([
//                         'application/json' => [
//                             'schema' => [
//                                          '$ref' => '#/components/schemas/Credentials',
//                             ],
//                         ],
//                     ]),
//                 ),
//             ),
//         );
//         $openApi->getPaths()->addPath('/api/login_check', $pathItem);

//         return $openApi;
//     }
// }