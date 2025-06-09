<?php
    use PHPUnit\Framework\TestCase;
    use Senna\AilosSdkPhp\API\AilosAPI;

    class TestAPICobrancaAuth extends TestCase
    {
        private AilosAPI $ailos;
        private string $clientId = 'hf6e389N7KzjyJggo8KUBxsdqPwa';
        private string $clientSecret = 'LFcgY_jH7W3bAWX8AyrbbTCXxMAa';

        private string $urlCallback = 'https://sdkailos.free.beeceptor.com/';
        private string $ailosApiKeyDeveloper = '365c74b4-2986-0122-e063-0a291434e16f';

        private string $loginCoopCode = '1';
        private string $loginAccountCode = '92312250';
        private string $loginPassword = 'aaaaa11111@';

        protected function setUp(): void
        {
            $this->ailos = new AilosAPI('https://apiendpointhml.ailos.coop.br/');
        }

        private function obtainAccessToken(): void
        {
            $response = $this->ailos->cobranca->auth->accessToken($this->clientId, $this->clientSecret, true);
            $body = $response->getBody()->getContents();
            $data = json_decode($body, true);

            $this->assertNotNull($data, "Resposta JSON inválida");
            $this->assertArrayHasKey('access_token', $data);
            $this->assertArrayHasKey('token_type', $data);
            $this->assertArrayHasKey('expires_in', $data);

            $this->assertNotEmpty($data['access_token']);
            $this->assertEquals('Bearer', $data['token_type']);
            $this->assertGreaterThan(0, $data['expires_in']);
        }

        private function obtainId(): string
        {
            $this->obtainAccessToken();

            $response = $this->ailos->cobranca->auth->id(
                $this->urlCallback,
                $this->ailosApiKeyDeveloper,
                'teste'
            );
            $bodyContents = (string) $response->getBody();

            $this->assertEquals(200, $response->getStatusCode());
            $this->assertNotEmpty($bodyContents);

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
            $this->obtainAccessToken();

            $id = $this->obtainId();

            $authResponse = $this->ailos->cobranca->auth->auth(
                $id,
                $this->loginCoopCode,
                $this->loginAccountCode,
                $this->loginPassword
            );

            $this->assertEquals(200, $authResponse->getStatusCode());
            $this->assertNotEmpty((string)$authResponse->getBody());
        }
    }

?>