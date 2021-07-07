# CIS Toolkit

A generic toolkit containing a lot of very useful PHP classes:

- String Toolkit
- Color Toolkit
- Api Toolkit
- Debug Toolkit
- File Toolkit
- HTML Selector Toolkit (for creation of selectors)
- Image Toolkit
- Map Toolkit (for mapping functions)
- Math Toolkit (for Math operations)
- Reflection Toolkit
- Sanitizer Toolkit (for variable sanitation)
- StringArtist Toolkit (for operations on strings)
- Url Toolkit

## Custom attributes

The toolkit contains various custom attributes for better documentation.

ClassInfo: For annotating class headers
Author: Describes an author

To validate attributes in your application do: `CisTools\Attribute\Validator\Validator::registerShutdownValidation()`

## Known issues

Color class is still in beta!
There is an issue with Alpha and some conversions.