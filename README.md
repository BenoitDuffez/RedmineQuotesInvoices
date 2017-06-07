# RedmineQuoteInvoice
Quote/Invoice Symfony project

## Purpose

As a freelancer, create quotes and invoices based off a Redmine database.

1. Create a user 'qiv' (or anything) on Redmine and enable its API key
1. Create a the custom fields on Redmine users table for some advanced user information (company name, identification, address, etc)
1. Add the user 'qiv' to the project you want a quote for
1. Create a new quote on the web tool
1. Export to PDF and send to the customer
1. After the customer has agreed to the quote, export the quote items into Redmine tickets for the specification
1. Convert a quote into one or multiple invoices

## Installation

1. git clone <repo>
1. cd <folder>
1. composer install
1. fill redmine and Symfony parameters
1. php bin/console doctrine:schema:update --force

Go to `/user/register` and create an admin account

# Project

Highly in WIP. Not useable by anyone but me atm. I'm planning to make this easier to use for other freelancers should the show interest for this project.

I initially intended to create this project as a Redmine plugin that would be tightly integrated into Redmine, but I can't stand rails.

