# Flook

*Disclaimer: This repository contains code developed during a hackathon and does not represent the quality of code produced in a professional environment.*

Flook is a project built during [HackGenY London 2015](http://hackgeny.com/london/) which won the third place prize and the Best 3D Project prize. Built using PHP/Laravel, it is a search engine for 3D models which works by scraping [Youmagine](https://www.youmagine.com/)'s search results. It then retrieves the 3D models and displays them interactively using the [Autodesk View and Data API](https://developer.autodesk.com/).

## Installation

To install this on your own machine:

1. Clone this repository to the desired folder
2. Run `composer install`
3. Run `php artisan key:generate`
4. Rename `.env.example` to `.env`
	1. Get your Autodesk View and Data API keys and paste them as appropriate

## Credits

- [Kelvin Zhang](https://github.com/kz)
- [Jack Barber](https://github.com/jbarber69)
- [Sasha Vorontsov](https://github.com/darkonious)
- [Freddie Rawlins](https://github.com/FreddieRa)
- [All Contributors](link-contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
