<?php
    use PHPUnit\Framework\TestCase;
    use Senna\AilosSdkPhp\AilosAPI;
    
    class AuthServiceTest extends TestCase
    {
        private AilosAPI $ailos;
        private string $clientId = 'hf6e389N7KzjyJggo8KUBxsdqPwa';
        private string $clientSecret = 'LFcgY_jH7W3bAWX8AyrbbTCXxMAa';

        protected function setUp(): void
        {
            $this->ailos = new AilosAPI('https://apiendpointhml.ailos.coop.br/');
        }

        private function obtainAccessToken(): array
        {
            $response = $this->ailos->auth->accessToken($this->clientId, $this->clientSecret);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $this->assertNotNull($data, "Resposta JSON inválida");
            $this->assertArrayHasKey('access_token', $data);
            $this->assertArrayHasKey('token_type', $data);
            $this->assertArrayHasKey('expires_in', $data);

            $this->assertNotEmpty($data['access_token']);
            $this->assertEquals('Bearer', $data['token_type']);
            $this->assertGreaterThan(0, $data['expires_in']);

            return $data;
        }

        private function obtainId(): string
        {
            $tokenData = $this->obtainAccessToken();

            $this->ailos->setDefaultHeader('Authorization', "{$tokenData['token_type']} {$tokenData['access_token']}");

            $response = $this->ailos->auth->id(
                'https://sdkailos.free.beeceptor.com',
                '365c74b4-2986-0122-e063-0a291434e16f',
                'teste'
            );
            $bodyContents = (string) $response->getBody();

            $this->assertEquals(200, $response->getStatusCode());
            $this->assertNotEmpty($bodyContents, "O corpo da resposta não pode ser vazio");

            return $bodyContents;
        }

        public function testAccessToken(): void
        {
            $this->obtainAccessToken();
        }

        public function testId(): void
        {
            $this->obtainId();
        }

        public function testAuth(): void
        {
            $tokenData = $this->obtainAccessToken();

            $this->ailos->setDefaultHeader('Authorization', "{$tokenData['token_type']} {$tokenData['access_token']}");

            $id = $this->obtainId();

            $authResponse = $this->ailos->auth->auth(
                $id,
                '1',
                '92312250',
                'aaaaa11111@'
            );

            $this->assertEquals(200, $authResponse->getStatusCode());
            $this->assertNotEmpty((string)$authResponse->getBody());
        }

    }

?>