# Color-constancy-captcha
A CAPTCHA (Completely Automated Public Turing test to tell Computers and Humans Apart) that tries to distinguish between humans and bots by applying on color constancy, which is difficult to reproduce by machines.

These programs are prototypes for performance evaluation experiments, rather than for preventing actual attacks from bots.

# Notice
This project was developed for the experiments in [1].

[1] Usuzaki, S., Aburada, K., Yamaba, H. et al. Proposal and evaluation for color constancy CAPTCHA, Artificial Life and Robotics (2021). https://doi.org/10.1007/s10015-021-00679-x

If you would like to know the images used in the experiment, please contact the author with their e-mail address on [1].

# Dependencies
## Ubuntu
`$ sudo apt install php-cli php-gd`

## CentOS
`$ sudo yum install php-cli php-gd`

# Getting Started
`$ git clone https://github.com/hexamp/color-constancy-captcha.git`

`$ cd color-constancy-captcha`

`$ php -S localhost:10000`
