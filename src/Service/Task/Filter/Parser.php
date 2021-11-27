<?php

declare(strict_types=1);

namespace App\Service\Task\Filter;

use App\Entity\User;
use App\Entity\UserSettings;
use App\Service\DateTime\DateTimeParser;
use App\Service\Task\Filter\Token\DateToken;
use App\Service\Task\Filter\Token\StringToken;
use App\Service\Task\Filter\Token\TagToken;
use App\Service\Task\Filter\Token\Token;
use function count;
use InvalidArgumentException;

class Parser
{
    public function __construct(private DateTimeParser $dateTimeParser)
    {
    }

    /**
     * @return iterable<Token>
     */
    public function parse(User $user, string $query): iterable
    {
        foreach (explode(' ', $query) as $token) {
            $t = $this->parseDateFilter($user->getSettings(), $token);
            if (null !== $t) {
                yield $t;

                continue;
            }

            $t = $this->parseTagFilter($token);
            if (null !== $t) {
                yield $t;

                continue;
            }

            yield new StringToken($token);
        }
    }

    private function parseDateFilter(UserSettings $settings, string $token): ?Token
    {
        $matches = [];
        preg_match('/^(created|updated|wait|started|ended|due)(>=|<=|>|<)(.*)$/', $token, $matches);
        if (4 !== count($matches)) {
            return null;
        }

        try {
            $date = $this->dateTimeParser->parse('Y-m-d H:i:s', 'Europe/Moscow', $matches[3]);
        } catch (InvalidArgumentException $e) {
            return null;
        }

        return new DateToken($matches[1], $matches[2], $date);
    }

    private function parseTagFilter(string $token): ?Token
    {
        if (str_starts_with($token, '+')) {
            return new TagToken(TagToken::MODE_INCLUDE, mb_substr($token, 1));
        } elseif (str_starts_with($token, '-')) {
            return new TagToken(TagToken::MODE_EXCLUDE, mb_substr($token, 1));
        }

        return null;
    }
}
