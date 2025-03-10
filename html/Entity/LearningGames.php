<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningGames
 *
 * @ORM\Table(name="learning_games")
 * @ORM\Entity
 */
class LearningGames
{

    use Timestamps;

    /**
     * @var int
     *
     * @ORM\Column(name="id_game", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idGame;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title = '';

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=65536, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start_date", type="date", nullable=true, options={"default"=NULL})
     */
    private $startDate = null;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end_date", type="date", nullable=true, options={"default"=NULL})
     */
    private $endDate = null;

    /**
     * @var string
     *
     * @ORM\Column(name="type_of", type="string", length=15, nullable=false)
     */
    private $typeOf = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_resource", type="integer", nullable=false)
     */
    private $idResource = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="play_chance", type="string", length=45, nullable=false)
     */
    private $playChance = '';


}
