Developer Notes
===

A lot of the classes in here are actually Spot mappers to domain concepts. Considering the simplicity of
the application, there doesn't look to be a benefit in separating these into purely logical objects; rather
leaving them as data-mapper coupled classes should be okay. However, it would be good to see them eventually migrated
into their own namespaces. `OpenCFP\Domain\Entity` is really a "in-transition" landing space for these.