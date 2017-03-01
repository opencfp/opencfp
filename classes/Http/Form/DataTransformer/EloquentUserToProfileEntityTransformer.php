<?php
namespace OpenCFP\Http\Form\DataTransformer;

use OpenCFP\Http\Form\Entity\Profile;
use Symfony\Component\Form\DataTransformerInterface;

class EloquentUserToProfileEntityTransformer implements DataTransformerInterface
{
    /**
     * Transforms an Eloquent user to a User Entity
     *
     * @param $eloquent_user
     * @return Profile
     */
    public function transform($eloquent_user)
    {
        if ($eloquent_user === null) {
            return null;
        }

        $profile = new Profile;
        $profile->setId($eloquent_user->id);
        $profile->setEmail($eloquent_user->email);
        $profile->setFirstName($eloquent_user->first_name);
        $profile->setLastName($eloquent_user->last_name);
        $profile->setCompany($eloquent_user->company);
        $profile->setTwitter($eloquent_user->twitter);
        $profile->setAirport($eloquent_user->airport);
        $profile->setHotel($eloquent_user->hotel);
        $profile->setBio($eloquent_user->bio);
        $profile->setInfo($eloquent_user->info);
        $profile->setPhotoPath($eloquent_user->photo_path);

        return $profile;
    }

    public function reverseTransform($user)
    {
        // @TODO figure out how to go back the other way
        return null;
    }
}