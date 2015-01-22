<?php
/*
* This file is part of the PierstovalCmsBundle package.
*
* (c) Alexandre "Pierstoval" Rock Ancelet <pierstoval@gmail.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Pierstoval\Bundle\CmsBundle\Entity;

use Sonata\MediaBundle\Entity\BaseMedia;

/**
 * @ORM\Entity
 * @ORM\Table(name="pierstoval_cms_medias")
 */
class Media extends BaseMedia {

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

}
