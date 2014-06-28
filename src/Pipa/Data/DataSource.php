<?php

namespace Pipa\Data;
use Psr\Log\LoggerAwareInterface;

/**
 * Defines a component that can intercommunicate with a data repository
 * and perform CRUD operations and simple queries
 */
interface DataSource extends LoggerAwareInterface {
	/**
	 * Returns a Collection resource
	 * 
	 * @return Collection
	 */
	function getCollection($name);
	
	/**
	 * Returns the resource or object representing the raw connection
	 * to the data source
	 */
	function getConnection();
	function getCriteria();
	
	function find(Criteria $criteria);
	function count(Criteria $criteria);
	function aggregate(Aggregate $aggregate, Criteria $criteria);
	
	function save(array $values, Collection $collection, $sequence = null);
	function update(array $values, Criteria $criteria);
	function delete(Criteria $criteria);
}
