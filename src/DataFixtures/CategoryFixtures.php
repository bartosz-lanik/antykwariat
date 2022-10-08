<?php

namespace App\DataFixtures;

use App\Entity\Category;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CategoryFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $exampleCategory1 = new Category();
        $exampleCategory1->translate('pl')->setName('Monety');
        $exampleCategory1->translate('en')->setName('Coins');

        $manager->persist($exampleCategory1);
        $exampleCategory1->mergeNewTranslations();
        $this->setReference('category1', $exampleCategory1);

        $exampleCategory2 = new Category();
        $exampleCategory2->translate('pl')->setName('Znaczki');
        $exampleCategory2->translate('en')->setName('Stamps');

        $manager->persist($exampleCategory2);
        $exampleCategory2->mergeNewTranslations();
        $this->setReference('category2', $exampleCategory2);

        $exampleCategory3 = new Category();
        $exampleCategory3->translate('pl')->setName('Pocztówki');
        $exampleCategory3->translate('en')->setName('Postcards');

        $manager->persist($exampleCategory3);
        $exampleCategory3->mergeNewTranslations();
        $this->setReference('category3', $exampleCategory3);

        $exampleCategory4 = new Category();
        $exampleCategory4->translate('pl')->setName('Zdjęcia');
        $exampleCategory4->translate('en')->setName('Photos');

        $manager->persist($exampleCategory4);
        $exampleCategory4->mergeNewTranslations();
        $this->setReference('category4', $exampleCategory4);

        $exampleCategory5 = new Category();
        $exampleCategory5->translate('pl')->setName('Inne');
        $exampleCategory5->translate('en')->setName('Other');

        $manager->persist($exampleCategory5);
        $exampleCategory5->mergeNewTranslations();
        $this->setReference('category5', $exampleCategory5);

        $manager->flush();
    }
}
