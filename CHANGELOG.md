# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## 1.7.0 - 2019-04-11

### Changed

- Reviewers can now no longer see email addresses of speakers
- SSO features updated to support changes to OpenCFP Central
- Docker configuration updated to use official images instead of custom ones

## 1.6.1 - 2018-11-06

### Changed

- Recent talks now ordered by newest-first
- Additional speaker info can now be submitted in Markdown
- Speakers now required to enter airport code if they request travel cost assistance
- Twitter URL's now correctly displayed in speaker lists 
- Additional information for a talk can now be viewed after the CFP has closed
- Speaker counts now only show speakers who have submitted talks


## 1.6.0 - 2018-10-12

### Added

- Users can now create accounts at [OpenCFP Central](https://www.opencfpcentral.com) for use with single-sign-on

### Changed

- Cleaned up generation of Twitter and joind.in links
- Picture uploads now handled using flysystem

## 1.5.9 - 2018-06-15

### Changed

- Environment now defaults to `production` if the environment variable has not been set
- Showing the contribution banner on the splash page now configurable

## 1.5.8 - 2018-05-26

### Added

- Users can now delete their own accounts, which deletes all associated information

## 1.5.7 - 2018-05-18

### Added

- Privacy policy now exists
- README updated to request that collected data not be shared with third parties without explicit consent of users

### Changed

- User accounts cannot be created without acknowledging they have read and understand the privacy policy 

## 1.5.6 - 2018-05-15

### Added

- instructions on how to build OpenCFP in a Docker container
- instructions on running OpenCFP in Homestead

### Changed

- Now running local scripts using COmposer

## 1.5.5 - 2018-04-23

### Added

- Speakers' joind.in URL's are now being validated better

### Changed

- Moved code from `classes` to `src` to better adhere to Symfony standards

## 1.5.4 - 2018-04-09

### Added

- Support for phly/keep-a-changelog to help with creating releases
- 'Personal Skills' is now a talk category

### Changed  

- Updated documentation about when dev server is available
- Updated documentation about permissions for cache and log folders

## 1.5.3 - 2018-03-27

### Added

- Filters for category and type on the talks admin page

### Changed

- Updated PHPUnit to 6.5.7
- Updated PHP CS Fixer to 2.10.4
- Updated Infection to 0.7.1

### Removed

- No longer using Code Climate for analyzing code
