<?php



namespace Formalms\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CoreRestAuthentication
 *
 * @ORM\Table(name="core_rest_authentication")
 * @ORM\Entity
 */
class CoreRestAuthentication
{
    /**
     * @var string
     *
     * @ORM\Column(name="token", type="string", length=255, nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $token = '';

    /**
     * @var int
     *
     * @ORM\Column(name="id_user", type="integer", nullable=false)
     */
    private $idUser = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="user_level", type="integer", nullable=false)
     */
    private $userLevel = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="generation_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $generationDate = '0000-00-00 00:00:00';

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_enter_date", type="datetime", nullable=true)
     */
    private $lastEnterDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiry_date", type="datetime", nullable=false, options={"default"="0000-00-00 00:00:00"})
     */
    private $expiryDate = '0000-00-00 00:00:00';


}
