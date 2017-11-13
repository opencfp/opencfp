<?php

namespace OpenCFP\Domain\Talk;

use OpenCFP\Domain\Entity\Talk;
use Spot\Locator;

class TalkFilter
{
    /** @var \OpenCFP\Domain\Entity\Mapper\Talk  */
    private $talk_mapper;
    
    public function __construct(Locator $spot)
    {
        $this->talk_mapper = $spot->mapper(Talk::class);
    }

    public function getFilteredTalks($admin_user_id, $filter = null, $options = [])
    {
        if ($filter === null) {
            return $this->talk_mapper->getAllPagerFormatted($admin_user_id, $options);
        }

        switch (strtolower($filter)) {
            case 'selected':
                return $this->talk_mapper->getSelected($admin_user_id, $options);

                break;

            case 'notviewed':
                return $this->talk_mapper->getNotViewedByUserId($admin_user_id, $options);

                break;

            case 'notrated':
                return $this->talk_mapper->getNotRatedByUserId($admin_user_id, $options);

                break;

            case 'toprated':
                return $this->talk_mapper->getTopRatedByUserId($admin_user_id, $options);

                break;

            case 'plusone':
                return $this->talk_mapper->getPlusOneByUserId($admin_user_id, $options);

                break;

            case 'viewed':
                return $this->talk_mapper->getViewedByUserId($admin_user_id, $options);

                break;

            case 'favorited':
                return $this->talk_mapper->getFavoritesByUserId($admin_user_id, $options);

                break;

            default:
                return $this->talk_mapper->getAllPagerFormatted($admin_user_id, $options);
        }
    }
}
