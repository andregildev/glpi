<?php
/**
 * ---------------------------------------------------------------------
 * GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2015-2020 Teclib' and contributors.
 *
 * http://glpi-project.org
 *
 * based on GLPI - Gestionnaire Libre de Parc Informatique
 * Copyright (C) 2003-2014 by the INDEPNET Development Team.
 *
 * ---------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GLPI.
 *
 * GLPI is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * GLPI is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 * ---------------------------------------------------------------------
 */

namespace Glpi\Application\View\Extension;

use CommonGLPI;
use Session;
use Twig\Extension\AbstractExtension;
use Twig\Extension\ExtensionInterface;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFunction;

/**
 * @since x.x.x
 */
class SessionExtension extends AbstractExtension implements ExtensionInterface, GlobalsInterface {

   public function getFunctions() {
      return [
         new TwigFunction('has_right', [$this, 'hasRight']),
      ];
   }

   public function getGlobals(): array {
      $user_name = Session::getLoginUserID()
         ? formatUserName(0, $_SESSION['glpiname'], $_SESSION['glpirealname'], $_SESSION['glpifirstname'])
         : '';

      return [
         'is_user_connected'        => Session::getLoginUserID() !== false,
         'is_debug_active'          => $_SESSION['glpi_use_mode'] ?? null === Session::DEBUG_MODE,
         'current_user_id'          => Session::getLoginUserID() || null,
         'current_user_name'        => $user_name,
         'use_simplified_interface' => Session::getCurrentInterface() === 'helpdesk',
      ];
   }

   /**
    * Check rights on item.
    *
    * @param string   $itemtype
    * @param int      $right
    * @param int|null $id
    *
    * @return bool
    *
    * @TODO Add a unit test.
    */
   public function hasRight(string $itemtype, int $right, ?int $id = null): bool {
      if (!is_a($itemtype, CommonGLPI::class, true)) {
         throw new \Exception(sprintf('Unable to check rights of item "%s".', $itemtype));
      }

      $item = new $itemtype();
      if ($id === null) {
         return $item->canGlobal($right);
      }

      return $item->can($id, $right);
   }
}
