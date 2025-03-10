<?php



namespace FormaLms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LearningGamesAccess
 *
 * @ORM\Table(name="learning_games_access", indexes={
 *      @ORM\Index(name="id_game_idx", columns={"id_game"}),
 *      @ORM\Index(name="idst_idx", columns={"idst"})
 * })
 * @ORM\Entity
 */
class LearningGamesAccess
{

    use Timestamps;
    
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="id_game", type="integer", nullable=false)
     
     */
    private $idGame = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="idst", type="integer", nullable=false)
     
     */
    private $idst = '0';


}
