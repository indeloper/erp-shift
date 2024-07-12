<?php

namespace App\Enumerates;

enum ProjectStatusEnum: int
{
    case CLIENT_REQUEST = 1;
    case VOLUME_CALCULATION = 2;
    case FORMATION_CP = 3;
    case COORDINATION_CP = 8;
    case FORMATION_CONTRACT = 4;
    case CONTRACTS_ASSIGNED = 7;
    case NOT_REALISED = 5;
    case CLOSED = 6;


    public static function labels(): array
    {
        return [
            self::CLIENT_REQUEST->value => 'Запрос от клиента',
            self::VOLUME_CALCULATION->value => 'Расчёт объёмов',
            self::FORMATION_CP->value => 'Формирование КП',
            self::COORDINATION_CP->value => 'Согласование КП с заказчиком',
            self::FORMATION_CONTRACT->value => 'Формирование договора',
            self::CONTRACTS_ASSIGNED->value => 'Договоры подписаны',
            self::NOT_REALISED->value => 'Не реализован',
            self::CLOSED->value => 'Закрыт',
        ];
    }

    public function label(): string
    {
        return self::labels()[$this->value];
    }

    public function description(): string
    {
        return match ($this) {
            self::CLIENT_REQUEST => 'Выявление потребностей заказчика и составление заявки на расчет объемов работ',
            self::VOLUME_CALCULATION => 'Формирование объемов работ инженером ПТО на основании заявки',
            self::FORMATION_CP => 'Оценка стоимости проекта, формирование и согласование коммерческого предложения с заказчиком',
            self::COORDINATION_CP => 'Согласование КП с заказчиком',
            self::FORMATION_CONTRACT => 'Формирование договоров на основании согласованного коммерческого предложения',
            self::CONTRACTS_ASSIGNED => 'Договоры подписаны. Проект готов к производству работ',
            self::NOT_REALISED => 'Не было достигнуто соглашения по коммерческому предложению или договорам',
            self::CLOSED => 'Проект перемещен в архив',
        };
    }
}
