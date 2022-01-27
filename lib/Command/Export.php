<?php

declare(strict_types=1);

/**
 * @copyright 2022 Christopher Ng <chrng8@gmail.com>
 *
 * @author Christopher Ng <chrng8@gmail.com>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Calendar\Command;

use OC\Core\Command\Base;
use OCA\Calendar\UserMigration\CalendarMigrator;
use OCP\IUser;
use OCP\IUserManager;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Export extends Base {

	/** @var IUserManager */
	private $userManager;

	/** @var CalendarMigrator */
	private $calendarMigrator;

	public function __construct(
		IUserManager $userManager,
		CalendarMigrator $calendarMigrator
	) {
		parent::__construct();
		$this->userManager = $userManager;
		$this->calendarMigrator = $calendarMigrator;
	}

	protected function configure() {
		$this
			->setName('calendar:export')
			->setDescription('Export the calendars of a user')
			->addArgument(
				'user',
				InputArgument::REQUIRED,
				'User to export',
			);
	}

	protected function execute(InputInterface $input, OutputInterface $output): int {
		$user = $this->userManager->get($input->getArgument('user'));

		if (!$user instanceof IUser) {
			$output->writeln('<error>User ' . $input->getArgument('user') . ' does not exist</error>');
			return 1;
		}

		try {
			$this->calendarMigrator->export($user, $output);
		} catch (\Exception $e) {
			$output->writeln('<error>' . $e->getMessage() . '</error>');
			return $e->getCode() !== 0 ? $e->getCode() : 1;
		}

		return 0;
	}
}
