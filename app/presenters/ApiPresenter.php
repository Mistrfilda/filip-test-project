<?php

declare(strict_types=1);


namespace App\Presenters;


use App\Model\Model;
use Nette\Application;
use Nette\Application\UI\Presenter;
use Nette\Utils\Validators;


class ApiPresenter extends Presenter
{
	/** @var Model */
	private $model;

	/** @var \ReflectionClass */
	private $modelReflection;

	/** @var int|null */
	private $requestId = NULL;

	public function __construct(Model $model)
	{
		parent::__construct();
		$this->model = $model;
		$this->modelReflection = new \ReflectionClass($this->model);
	}

	public function actionDefault()
	{
		//{"jsonrpc": "2.0", "method": "save", "params": {"gameId": 1, "userId": 2, "score": 3}, "id": 50}
		//{"jsonrpc": "2.0", "method": "retrieveBestPlayers", "params": {"gameId": 1}, "id": 55}

		$request = json_decode($this->getHttpRequest()->getRawBody(), TRUE);

		if ($request === NULL) {
			$this->sendErrorResponse(-32700, "Invalid JSON");
		}

		if (array_key_exists('id', $request)) {
			$this->requestId = $request['id'];
		}

		if (!is_array($request) || !array_key_exists('jsonrpc', $request) || !array_key_exists('method', $request) || !array_key_exists('params', $request) || !is_array($request['params'])) {
			$this->sendErrorResponse(-32700, "Invalid JSON");
		}

		if (!$this->validateMethod($request['method'])) {
			$this->sendErrorResponse(-32601, "Method not found");
		}

		$this->validateParameters($request['method'], $request['params']);

		$methodCall = call_user_func_array([$this->model, $request['method']], $request['params']);

		$this->sendSuccessResponse($methodCall);
	}

	private function validateMethod(string $method) : bool
	{
		try {
			$this->modelReflection->getMethod($method);
		} catch (\ReflectionException $e) {
			return FALSE;
		}

		return TRUE;
	}

	private function validateParameters(string $method, array $parameters) : bool
	{
		$methodParameters = $this->modelReflection->getMethod($method)->getParameters();
		foreach ($methodParameters as $parameter) {
			if (!array_key_exists($parameter->getName(), $parameters)) {
				$this->sendErrorResponse(-32602, "Parse error, missing parameter - " . $parameter->getName());
			}

			if (!Validators::is($parameters[$parameter->getName()], $parameter->getType()->getName())) {
				$this->sendErrorResponse(-32602, sprintf('Parse error, wrong parameter type for  - %s, expected - %s', $parameter->getName(), $parameter->getType()->getName()));
			}
		}

		return TRUE;
	}

	private function sendErrorResponse(int $code, string $message) : void
	{
		$rpcResponse = [
			'jsonrpc' => 2.0,
			'error'   => [
				'code'    => $code,
				'message' => $message,
			],
			'id'      => $this->requestId,
		];

		$this->sendResponse(new Application\Responses\JsonResponse(json_encode($rpcResponse)));
	}

	private function sendSuccessResponse($result) : void
	{
		$rpcResponse = [
			'jsonrpc' => 2.0,
			'result'  => $result,
			'id'      => $this->requestId,
		];

		$this->sendResponse(new Application\Responses\JsonResponse(json_encode($rpcResponse)));
	}

}