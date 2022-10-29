<?php

namespace App\DataFixtures;
use App\Entity\Product;
use App\Entity\Category;
use App\Entity\Order;
use App\Entity\User;
use Faker\Factory;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\DBAL\Driver\IBMDB2\Exception\Factory as ExceptionFactory;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
                
    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder ;    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }



    public function load(ObjectManager $manager): void
    {
        // $product = new Product();
        // $manager->persist($product);
        $faker = Factory::create();
        //$manager->persist($product);
        for ($p = 0; $p < 6; $p++) {
            $category = new Category;
            $category->setName($faker->firstname);
            $manager->persist($category);
             
            $user = new User();
            $hash = $this->encoder->encodePassword($user,"password");
            // $user->setfirstName($faker->firstName);
            // $user->setlastName($faker->lastName);
            $user->setAdresse($faker->address);
            $user->setEmail($faker->email);
            $user->setPassword($hash);
            $user->setCin($faker->randomNumber);
            $manager->persist($user);
            // $user = new User;
            // $user->setEmail($faker->email);
            // $user->setPassword($faker->password);
            // $user->setAdresse($faker->address);
            // $user->setCin('00321541');
            // $manager->persist($user);

            for ($c = 0; $c <mt_rand(3, 15); $c++) {
                $product = new Product();
                $product->setName($faker->firstName);
                $product->setImage($faker->text);
                $product->setPrice($faker->randomNumber(2));
                $product->setDescription($faker->text);
                $product->setQuantity($faker->randomNumber(5));
                $product->setCategory($category);
                $manager->persist($product);

                $order= new Order();
                $order->setDate($faker->dateTime());
                $order->setTotal(1234.44);
                $order->setUser($user);
                $manager->persist($order);
                
            }
            // $manager->persist($category);
        }
        $manager->flush();
    }
}
