<?php

/**
 * Copyright 2020-2022 LiTEK - Josewowgame2888
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

declare(strict_types=1);
namespace litek\pffa\form\elements;

use JsonSerializable;
use pocketmine\form\FormValidationException;
use function is_int;

abstract class Element implements JsonSerializable
{
	/** @var string */
	protected $text;
	/** @var mixed */
	protected $value;

	/**
	 * @param string $text
	 */
	public function __construct(string $text)
	{
		$this->text = $text;
	}

	/**
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}

	/**
	 * @return array
	 */
	final public function jsonSerialize(): array
	{
		$array = ["text" => $this->getText()];
		if ($this->getType() !== null) {
			$array["type"] = $this->getType();
		}
		return $array + $this->serializeElementData();
	}

	/**
	 * @return string
	 */
	public function getText(): string
	{
		return $this->text;
	}

	/**
	 * @return string|null
	 */
	abstract public function getType(): ?string;

	/**
	 * @return array
	 */
	abstract public function serializeElementData(): array;

	/**
	 * @param mixed $value
	 */
	public function validate($value): void
	{
		if (!is_int($value)) {
			throw new FormValidationException("Expected int, got " . gettype($value));
		}
	}
}