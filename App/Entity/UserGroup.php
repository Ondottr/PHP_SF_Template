<?php /** @noinspection MethodShouldBeFinalInspection */
declare( strict_types=1 );
/*
 * Copyright © 2018-2024, Nations Original Sp. z o.o. <contact@nations-original.com>
 *
 * Permission to use, copy, modify, and/or distribute this software for any purpose with or without fee is hereby
 * granted, provided that the above copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED \"AS IS\" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH REGARD TO THIS SOFTWARE
 * INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE
 * LIABLE FOR ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER
 * RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR OTHER
 * TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
 */

namespace App\Entity;

use App\Repository\UserGroupRepository;
use Doctrine\ORM\Mapping as ORM;
use PHP_SF\System\Attributes\Validator\TranslatablePropertyName;
use PHP_SF\System\Classes\Abstracts\AbstractEntity;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity( repositoryClass: UserGroupRepository::class, readOnly: true )]
#[ORM\Table( name: 'user_groups' )]
#[ORM\Cache( usage: 'READ_ONLY' )]
class UserGroup extends AbstractEntity
{

    #[TranslatablePropertyName( 'Name' )]
    #[ORM\Column( type: 'string', unique: true )]
    #[Groups( groups: [ 'read' ] )]
    protected string $name;


    public function getName(): string
    {
        return $this->name;
    }

    public function getLifecycleCallbacks(): array
    {
        return [];
    }

}
