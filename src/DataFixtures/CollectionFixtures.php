<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\MyCollection;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CollectionFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        /* @var Category $category */
        for($i=1;$i<=70;$i++)
        {
            $title = 'Random object number '.$i;
            $category = $this->getReference('category'.rand(1,5));
            $myCollection = new MyCollection();
            $myCollection->setName($title);
            $myCollection->setSlug($this->slugFromName($title));
            $myCollection->setDescription('Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. It has survived not only five centuries, but also the leap into electronic typesetting, remaining essentially unchanged. It was popularised in the 1960s with the release of Letraset sheets containing Lorem Ipsum passages, and more recently with desktop publishing software like Aldus PageMaker including versions of Lorem Ipsum.');
            $myCollection->setCategory($category);
            $manager->persist($myCollection);

            $manager->flush();
        }
    }

    public function getDependencies(): array
    {
        return [
            CategoryFixtures::class
        ];
    }

    private function slugFromName(string $text): string
    {
        $text = iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
        return trim(strtolower(trim(preg_replace('/[^a-z0-9]+/i', '-', $text))), '-');
    }
}
