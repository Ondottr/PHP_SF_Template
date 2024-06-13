<?php

return [
    Symfony\Bundle\FrameworkBundle\FrameworkBundle::class        => [ 'all' => true ],
    Symfony\Bundle\TwigBundle\TwigBundle::class                  => [ 'all' => true ],
    Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class         => [ 'all' => true ],
    Symfony\Bundle\DebugBundle\DebugBundle::class                => [ 'dev' => true, 'test' => true ],
    Symfony\Bundle\MakerBundle\MakerBundle::class                => [ 'dev' => true ],
    Twig\Extra\TwigExtraBundle\TwigExtraBundle::class            => [ 'all' => true ],
    Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => [ 'all' => true ],
    Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class    => [ 'dev' => true, 'test' => true ],
    Nelmio\ApiDocBundle\NelmioApiDocBundle::class                => [ 'all' => true ],
];
